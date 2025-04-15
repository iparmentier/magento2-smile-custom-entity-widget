<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_SmileCustomEntityWidget
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Model\Rule\Condition;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Backend\Helper\Data as BackendData;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\Table as TableSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\Rule\Model\Condition\Context;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Smile\ScopedEav\Api\Data\EntityInterface;
use Smile\CustomEntity\Api\Data\CustomEntityInterface;
use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;
use Smile\CustomEntity\Model\CustomEntity\Attribute as CustomEntityAttribute;
use Psr\Log\LoggerInterface;

/**
 * Rule smile custom entity condition data model
 *
 * This class handles custom entity conditions for Magento rule processing.
 * It supports filtering entities based on various attribute types and conditions.
 */
class Entity extends AbstractProduct implements ResetAfterRequestInterface
{
    /**
     * {@inheritdoc}
     */
    protected $elementName = 'parameters';

    /**
     * @var array Attributes that have been joined to the collection
     */
    protected array $joinedAttributes = [];

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array List of attribute codes to exclude from condition options
     */
    private array $excludeAttributes = [
        CustomEntityInterface::URL_KEY,
        'image'
    ];

    /**
     * @var array|null Cache for custom entity attributes
     */
    private static ?array $customEntityAttributes = null;

    /**
     * Constructor
     *
     * @param Context $context Rule condition context
     * @param BackendData $backendData Backend helper data
     * @param EavConfig $config EAV configuration
     * @param ProductFactory $productFactory Product factory
     * @param ProductRepositoryInterface $productRepository Product repository
     * @param ProductResource $productResource Product resource model
     * @param AttributeSetCollection $attrSetCollection Attribute set collection
     * @param FormatInterface $localeFormat Locale format interface
     * @param StoreManagerInterface $storeManager Store manager
     * @param LoggerInterface $logger PSR logger
     * @param array $data Additional data
     * @param ProductCategoryList|null $categoryList Product category list
     */
    public function __construct(
        Context $context,
        BackendData $backendData,
        EavConfig $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        AttributeSetCollection $attrSetCollection,
        FormatInterface $localeFormat,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        array $data = [],
        ?ProductCategoryList $categoryList = null
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data,
            $categoryList
        );

        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array<string, string[]>
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();

            $this->_defaultOperatorInputByType = [
                'string'      => ['==', '!=', '{}', '!{}', '()', '!()'],
                'numeric'     => ['==', '!=', '>=', '>', '<=', '<'],
                'date'        => ['==', '>=', '>', '<=', '<'],
                'select'      => ['==', '!='],
                'boolean'     => ['==', '!='],
                'multiselect' => ['()', '!()']
            ];

            $this->_arrayInputTypes[] = 'multiselect'; // Ensure multiselect is treated as array type
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * Retrieve attribute object
     *
     * Override to intercept attributes and force source model if needed.
     * Return null on failure instead of DataObject.
     *
     * @return AbstractAttribute|CustomEntityAttribute|null
     */
    public function getAttributeObject()
    {
        $code = $this->getAttribute();
        $attribute = null;

        try {
            $attribute = $this->_config->getAttribute(CustomEntityAttributeInterface::ENTITY_TYPE_CODE, $code);

            // Bugfix: Ensure multiselect attributes have a source model
            if ($attribute->getFrontendInput() == 'multiselect' && !$attribute->getData('source_model')) {
                $attribute->setData('source_model', TableSource::class);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error loading attribute: ' . $e->getMessage(), [
                'attribute_code' => $code,
                'entity_type' => CustomEntityAttributeInterface::ENTITY_TYPE_CODE
            ]);
        }

        return $attribute;
    }

    /**
     * Add special attributes to the attribute list
     *
     * @param array<string, string> &$attributes Attributes array to modify
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes): void
    {
        $attributes['entity_id'] = __('Entity ID');
        $attributes['has_image'] = __('Entity has image');
    }

    /**
     * Load attribute options for the condition
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributeList = $this->_config->getEntityType(CustomEntityAttributeInterface::ENTITY_TYPE_CODE)
            ->getAttributeCollection()
            ->addFieldToFilter('frontend_label', ['neq' => ''])
            ->addFieldToFilter('attribute_code', ['nin' => $this->excludeAttributes]);

        $attributes = [];
        $this->_addSpecialAttributes($attributes);

        /** @var AbstractAttribute|CustomEntityAttribute $attribute */
        foreach ($attributeList as $attribute) {
            $label = sprintf('%s (%s)', $attribute->getFrontendLabel(), $attribute->getAttributeCode());
            $attributes[$attribute->getAttributeCode()] = $label;
        }

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Add condition to collection
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder Search criteria builder
     * @return $this
     */
    public function addToCollection(SearchCriteriaBuilder $searchCriteriaBuilder): self
    {
        $code = $this->getAttribute();
        $attribute = $this->getAttributeObject();

        if (!$code || !$attribute) {
            return $this;
        }

        $conditionValue = $this->getValueParsed();
        $operatorType = $this->getOperatorType();

        if ($code === 'has_image') {
            $filterValue = ($conditionValue == 1);

            $searchCriteriaBuilder->addFilter(
                EntityInterface::IMAGE,
                $filterValue,
                $filterValue ? 'notnull' : 'null'
            );

            return $this;
        }

        /*
         * Ensure value is suitable for the operator.
         * E.g., 'in' and 'nin' require arrays.
         */
        if (in_array($operatorType, ['in', 'nin']) && !is_array($conditionValue)) {
            $conditionValue = array_filter(array_map('trim', explode(',', (string)$conditionValue)));
            if (empty($conditionValue)) {
                return $this;
            }
        } elseif (is_array($conditionValue) && !in_array($operatorType, ['in', 'nin'])) {
            $conditionValue = reset($conditionValue);
        }

        /**
         * Convert attribute values to integers for select/multiselect attributes.
         * This is crucial because the filter will not work for these attribute types
         */
        if ($attribute instanceof AbstractAttribute || $attribute instanceof CustomEntityAttribute) {
            $frontendInput = $attribute->getFrontendInput();
            if (in_array($frontendInput, ['select', 'multiselect']) && is_array($conditionValue)) {
                $conditionValue = array_map('intval', $conditionValue);
            }
        }

        $filterValue = $conditionValue;
        if (in_array($operatorType, ['like', 'nlike']) && is_string($filterValue)) {
            $filterValue = str_replace(['%', '_'], ['\%', '\_'], $filterValue);
            $filterValue = '%' . $filterValue . '%';
        }

        if ($filterValue !== null && $filterValue !== '' && $filterValue !== []) {
            $searchCriteriaBuilder->addFilter($code, $filterValue, $operatorType);
        }

        return $this;
    }

    /**
     * Get the condition type for SearchCriteriaBuilder based on the operator.
     *
     * @return string A condition type compatible with SearchCriteriaBuilder::addFilter
     */
    public function getOperatorType(): string
    {
        $operator = $this->getOperator();

        return match ($operator) {
            '>' => 'gt',
            '>=' => 'gte',
            '<' => 'lt',
            '<=' => 'lte',
            '()', '==' => 'in',
            '!()', '!=' => 'nin',
            '{}' => 'like',
            '!{}' => 'nlike',
            default => $this->isArrayOperatorType() ? 'in' : 'eq'
        };
    }

    /**
     * Collect validated attributes for SearchCriteriaBuilder
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder Search criteria builder
     * @return $this
     */
    public function collectValidatedAttributes($searchCriteriaBuilder)
    {
        return $this->addToCollection($searchCriteriaBuilder);
    }

    /**
     * Retrieve input type for attribute
     *
     * @return string
     */
    public function getInputType(): string
    {
        if ($this->getAttribute() === 'has_image') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        if ($this->getAttribute() === 'entity_id') {
            return 'string';
        }

        $frontendInput = $this->getAttributeObject()->getFrontendInput();
        return match ($frontendInput) {
            'select' => 'select',
            'multiselect' => 'multiselect',
            'date' => 'date',
            'boolean' => 'boolean',
            default => 'string'
        };
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType(): string
    {
        if ($this->getAttribute() === 'has_image') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        if ($this->getAttribute() === 'entity_id') {
            return 'text';
        }

        $frontendInput = $this->getAttributeObject()->getFrontendInput();
        return match ($frontendInput) {
            'select', 'boolean' => 'select',
            'multiselect' => 'multiselect',
            'date' => 'date',
            default => 'text',
        };
    }

    /**
     * Prepares values options to be used as select options or hashed array
     *
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return $this
     */
    protected function _prepareValueOptions(): self
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        $selectOptions = null;
        if ($this->getAttribute() === 'has_image') {
            $selectOptions = [
                ['value' => 0, 'label' => __('No')],
                ['value' => 1, 'label' => __('Yes')]
            ];

            $this->setData('value_select_options', $selectOptions);
            $this->setData('value_option', array_column($selectOptions, 'label', 'value'));

            return $this;
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $source = $attributeObject->getSource();
                if ($source) {
                    $selectOptions = $source->getAllOptions($addEmptyOption, true);
                }
            }
        }

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    /**
     * Reset internal state after request
     *
     * @return void
     */
    public function _resetState(): void
    {
        $this->joinedAttributes = [];
    }
}