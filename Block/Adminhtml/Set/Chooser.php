<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Block\Adminhtml\Set;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Eav\Model\Config;
use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;

/**
 * Attribute Set Chooser
 */
class Chooser extends Extended
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Magento\Eav\Model\Config $eavConfig
     * @param Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        $this->attributeSetRepository = $attributeSetRepository;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('attribute_set_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_is_active' => '1']);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('custom_entity_widget/set/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Chooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $attributeSet = $this->attributeSetRepository->get((int) $element->getValue());
            if ($attributeSet->getId()) {
                $chooser->setLabel($this->escapeHtml($attributeSet->getAttributeSetName()));
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();

        $js = '
            function (grid, event) {

                var trElement = Event.findElement(event, "tr");
                var customEntitySetTitle = trElement.down("td").next().innerHTML;
                var customEntitySetId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");

            ' . $chooserJsObject . '.setElementValue(customEntitySetId);
            ' . $chooserJsObject . '.setElementLabel(customEntitySetTitle);
            ' . $chooserJsObject . '.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare pages collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $entityTypeId = CustomEntityAttributeInterface::ENTITY_TYPE_CODE;
        $entityType = $this->eavConfig->getEntityType($entityTypeId);
        $this->setCollection($entityType->getAttributeSetCollection());
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for slider grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'chooser_id',
            [
                'header' => __('ID'),
                'index' => 'attribute_set_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'chooser_title',
            [
                'header' => __('Title'),
                'index' => 'attribute_set_name',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title'
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                [
                    'header' => __('Store View'),
                    'index' => 'store_id',
                    'type' => 'store',
                    'store_all' => true,
                    'store_view' => true,
                    'sortable' => false,
                    'filter_condition_callback' => [$this, '_filterStoreCondition']
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'custom_entity_widget/set/chooser',
            [
                '_current' => true,
                'uniq_id' => $this->getId()
            ]
        );
    }
}