<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_SmileCustomEntityWidget
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Rule\Model\AbstractModel as RuleAbstractModel;

/**
 * Rule for catalog widget
 *
 * @api
 * @since 100.0.2
 */
class Rule extends RuleAbstractModel
{
    /**
     * @var \Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\CombineFactory
     */
    protected $conditionsFactory;

    /**
     * Rule constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\CombineFactory $conditionsFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     *
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\CombineFactory $conditionsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->conditionsFactory = $conditionsFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsInstance()
    {
        return $this->conditionsFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsInstance()
    {
        return null;
    }
}