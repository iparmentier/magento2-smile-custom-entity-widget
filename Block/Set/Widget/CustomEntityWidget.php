<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_SmileCustomEntityWidget
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Block\Set\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Widget\Block\BlockInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Artbambou\SmileCustomEntityWidget\Model\Rule;
use Magento\Widget\Helper\Conditions;
use Smile\CustomEntity\Api\CustomEntityRepositoryInterface;
use Smile\CustomEntity\Api\Data\CustomEntityInterface;
use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;
use Smile\CustomEntity\Block\CustomEntity\ImageFactory;
use Smile\CustomEntity\Model\CustomEntity;
use Artbambou\SmileCustomEntityWidget\Model\Config\Source\SortBy;

class CustomEntityWidget extends Template implements BlockInterface
{
    /**
     * Default imge width in pixel
     */
    const DEFAULT_IMAGE_WIDTH = 200;

    /**
     * Default image height in pixel
     */
    const DEFAULT_IMAGE_HEIGHT = 200;

    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_ITEMS_COUNT = 8;

    /**
     * Name of request parameter for page number value
     *
     * @deprecated @see $this->getData('page_var_name')
     */
    const PAGE_VAR_NAME = 'sp';

    /**
     * Default value for products per page
     */
    const DEFAULT_ITEMS_PER_PAGE = 4;

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER = false;

    /**
     * Instance of pager block
     *
     * @var Pager
     */
    protected $pager;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var CustomEntityRepositoryInterface
     */
    private $customEntityRepository;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var Conditions
     */
    protected $conditionsHelper;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var AttributeSetInterface $attributeSet
     */
    private $attributeSet;

    /**
     * @var \Smile\CustomEntity\Api\Data\CustomEntityInterface[]|null
     */
    private $entities;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $serializer;

    /**
     * View constructor.
     *
     * @param Template\Context $context
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CustomEntityRepositoryInterface $customEntityRepository
     * @param EavConfig $eavConfig
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SortOrderBuilder|null $sortOrderBuilder
     * @param Rule $rule
     * @param Conditions $conditionsHelper
     * @param ImageFactory $imageFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CustomEntityRepositoryInterface $customEntityRepository,
        EavConfig $eavConfig,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SortOrderBuilder $sortOrderBuilder,
        Rule $rule,
        Conditions $conditionsHelper,
        ImageFactory $imageFactory,
        array $data = [],
        Json $serializer = null
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->customEntityRepository = $customEntityRepository;
        $this->eavConfig = $eavConfig;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->rule = $rule;
        $this->conditionsHelper = $conditionsHelper;
        $this->imageFactory = $imageFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
	protected function _construct()
	{
		parent::_construct();

		$this->addData(
			[
				'cache_lifetime' => 86400,
				'cache_tags' => [
                    CustomEntity::CACHE_TAG
                ]
			]
		);
	}

    /**
     * Get key pieces for caching block content
     *
     * @return array
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {
        $conditions = $this->getData('conditions')
            ? $this->getData('conditions')
            : $this->getData('conditions_encoded');

        return [
           'AB_CUSTOM_ENTITY_WIDGET',
           $this->_storeManager->getStore()->getId(),
           $this->_design->getDesignTheme()->getId(),
           $this->getImageWidth(),
           $this->getImageHeight(),
           $this->canShowFooterButton(),
           (int)$this->getRequest()->getParam($this->getData('page_var_name'), 1),
           $this->getItemsPerPage(),
           $this->getItemsCount(),
           $conditions,
           $this->serializer->serialize($this->getRequest()->getParams()),
           $this->getTemplate()
        ];
    }

    /**
     * Prepare to html
     * Check has custom entity
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getEntities()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Return custom entities.
     *
     * @return \Smile\CustomEntity\Api\Data\CustomEntityInterface[]
     */
    public function getEntities()
    {
        if (!$this->entities) {
            /** @var SearchCriteriaBuilderFactory $searchCriteriaBuilder */
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

            $searchCriteriaBuilder->addFilter(
                'attribute_set_id',
                $this->getAttributeSet()->getAttributeSetId()
            );

            $searchCriteriaBuilder->addFilter(
                'is_active',
                true
            );

            $conditions = $this->getConditions();
            $conditions->collectValidatedAttributes($searchCriteriaBuilder);

            /** @var SortOrderBuilder $sortOrderBuilder */
            $sortOrder = $this->sortOrderBuilder->setField($this->getSortBy())
                ->setDirection($this->getSortDirection())
                ->create();
            $searchCriteriaBuilder->setSortOrders([$sortOrder]);

            if ($this->showPager()) {
                $this->getPager()->addCriteria($searchCriteriaBuilder);
                $searchResult = $this->customEntityRepository->getList($searchCriteriaBuilder->create());
                $this->getPager()->setSearchResult($searchResult);
            } else {
                $searchResult = $this->customEntityRepository->getList(
                    $searchCriteriaBuilder->create()->setPageSize($this->getItemsCount())
                );
            }

            $this->entities = $searchResult->getItems();
        }

        return $this->entities;
    }

    /**
     * Return attribute set
     *
     * @return bool|AttributeSetInterface
     */
    public function getAttributeSet()
    {
        if (! $this->attributeSet) {
            if ($this->hasData('attribute_set_id')) {
                $attributeSetId = $this->getData('attribute_set_id');
            } else {
                $entityType = $this->eavConfig->getEntityType(CustomEntityAttributeInterface::ENTITY_TYPE_CODE);
                $attributeSetId = $entityType->getDefaultAttributeSetId();
            }

            $attributeSet = $this->attributeSetRepository->get($attributeSetId);
            $this->attributeSet = $attributeSet;
        }
        return $this->attributeSet;
    }

    /**
     * Return attribute set url.
     *
     * @return string
     */
    public function getAttributeSetUrl()
    {
        $customEntity = end($this->entities);
        return $this->_urlBuilder->getDirectUrl($customEntity->getAttributeSetUrlKey());
    }

    /**
     * Return custom entity image.
     *
     * @return string
     */
    public function getImage($entity)
    {
        return $this->imageFactory->create($entity)->toHtml();
    }

    /**
     * Return entity url.
     *
     * @return string
     */
    public function getEntityUrl($entity)
    {
        return $this->_urlBuilder->getDirectUrl($entity->getUrlPath());
    }

    /**
     * Retrieve how many items should be displayed
     *
     * @return int
     */
    public function getItemsCount()
    {
        if ($this->hasData('items_count')) {
            return $this->getData('items_count');
        }

        if (null === $this->getData('items_count')) {
            $this->setData('items_count', self::DEFAULT_ITEMS_COUNT);
        }

        return $this->getData('items_count');
    }

    /**
     * Retrieve how many items should be displayed
     *
     * @return int
     */
    public function getItemsPerPage()
    {
        if (!$this->hasData('items_per_page')) {
            $this->setData('items_per_page', self::DEFAULT_ITEMS_PER_PAGE);
        }
        return $this->getData('items_per_page');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Retrieve how many items should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        return $this->showPager() ? $this->getItemsPerPage() : $this->getItemsCount();
    }

    /**
     * Render pager
     *
     * @return Pager
     */
    public function getPager()
    {
        if ($this->showPager()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    \Smile\CustomEntity\Block\Html\Pager::class,
                    $this->getWidgetPagerBlockName()
                );

                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getData('page_var_name') ?: self::PAGE_VAR_NAME)
                    ->setLimit($this->getItemsPerPage())
                    ->setTotalLimit($this->getItemsCount());
            }
            if ($this->pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->pager;
            }
        }
        return '';
    }

    /**
     * Render pagination HTML
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPagerHtml()
    {
        if ($this->showPager()) {
            if ($this->pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->pager->toHtml();
            }
        }
        return '';
    }

    /**
     * Sort by
     *
     * @return string
     */
    public function getSortBy()
    {
        $sortBy = $this->getData('sort_by');
        if (!in_array($sortBy, array_keys(SortBy::toArray()))) {
            $sortBy = 'name';
        }
        return $sortBy;
    }

    /**
     * Sort direction
     *
     * @return string
     */
    public function getSortDirection()
    {
        $direction = SortOrder::SORT_DESC;
        if ($this->getData('sort_direction')) {
            $direction = strtoupper((string)$this->getData('sort_direction'));
        }
        if (!in_array($direction, [SortOrder::SORT_DESC, SortOrder::SORT_ASC])) {
            $direction = SortOrder::SORT_DESC;
        }
        return $direction;
    }

    /**
    * Get the width of image
	*
    * @return int
    */
    public function getImageWidth()
    {
        if ($this->hasData('image_width')) {
            return $this->getData('image_width');
        }
        return self::DEFAULT_IMAGE_WIDTH;
    }

    /**
    * Get the height of image
	*
    * @return int
    */
    public function getImageHeight()
    {
        if ($this->hasData('image_height')) {
            return $this->getData('image_height');
        }
        return self::DEFAULT_IMAGE_HEIGHT;
    }

    /**
    * Show url attribute set custom entity
	*
    * @return bool
    */
    public function canShowFooterButton()
    {
        if ($this->hasData('show_footer_button')) {
            return $this->getData('show_footer_button');
        }
        return false;
    }

    /**
    * Get footer button text
	*
    * @return bool
    */
    public function getTextFooterButton()
    {
        if ($this->hasData('text_footer_button')) {
            return $this->getData('text_footer_button');
        }
        return __('Discover');
    }

    /**
    * Get footer button title
	*
    * @return bool
    */
    public function getTitleFooterButton()
    {
        if ($this->hasData('title_footer_button')) {
            return $this->getData('title_footer_button');
        }
        return false;
    }

    /**
     * Get conditions
     *
     * @return Combine
     */
    public function getConditions()
    {
        $conditions = $this->getData('conditions_encoded')
            ? $this->getData('conditions_encoded')
            : $this->getData('conditions');

        if (is_string($conditions)) {
            $conditions = $this->decodeConditions($conditions);
        }

        $this->rule->loadPost(['conditions' => $conditions]);
        return $this->rule->getConditions();
    }

    /**
     * Return block identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getEntities()) {
            foreach ($this->getEntities() as $entity) {
                $identities[] = $entity->getIdentities();
            }
        }

        if ($attributeSet = $this->getAttributeSet()) {
            $identities[] = CustomEntity::CACHE_CUSTOM_ENTITY_SET_TAG . '_' . $attributeSet->getAttributeSetId();
        }

        return $identities;
    }

    /**
     * Get widget block name
     *
     * @return string
     */
    private function getWidgetPagerBlockName()
    {
        $pageName = $this->getData('page_var_name');
        $pagerBlockName = 'widget.smile.set.list.pager';
        if (!$pageName) {
            return $pagerBlockName;
        }

        return $pagerBlockName . '.' . $pageName;
    }

    /**
     * Decode widget conditions.
     *
     * @param string $encodedConditions Conditions encoded as JSON.
     * @return array<mixed> Decoded conditions array.
     * @see \Magento\Widget\Model\Widget::getDirectiveParam
     */
    public function decodeConditions(string $encodedConditions): array
    {
        try {
            $conditions = $this->serializer->unserialize(htmlspecialchars_decode($encodedConditions));
            return is_array($conditions) ? $conditions : [];
        } catch (\InvalidArgumentException $exception) {
            /** @var array{exception:\Throwable, encoded_conditions:string, uri:string} $context */
            $context = [
                'exception' => $exception,
                'encoded_conditions' => $encodedConditions,
                'uri' => $this->_request->getRequestUri(),
            ];
            $this->_logger->error($exception->getMessage(), $context);
            return [];
        }
    }
}
