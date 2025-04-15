<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_SmileCustomEntityWidget
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Controller\Adminhtml\Set\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\View\LayoutFactory;


class LoadConditions extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ElementFactory
     */
    protected $elementFactory;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param ElementFactory $elementFactory
     * @param FormFactory $formFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        ElementFactory $elementFactory,
        FormFactory $formFactory
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->elementFactory = $elementFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * Load conditions HTML for selected attribute set
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $attributeSetId = $this->getRequest()->getParam('attribute_set_id');
        if (!$attributeSetId) {
            return $result->setData(['success' => false, 'message' => __('Attribute Set ID is required')]);
        }

        $formElement = $this->getRequest()->getParam('form');
        if (!$formElement) {
            return $result->setData(['success' => false, 'message' => __('Form ID is required')]);
        }

        try {
            $layout = $this->layoutFactory->create();

            $formElementId = $formElement ?: 'smile_ce_widget_conditions_container_' . uniqid();
            $element = $this->elementFactory->create('text');
            $element->setForm(new DataObject());
            $element->setContainer(
                new DataObject(['html_id' => $formElementId])
            );
            $conditionsBlock = $layout->createBlock(
                \Artbambou\SmileCustomEntityWidget\Block\Set\Widget\Conditions::class
            );
            $conditionsBlock->setAttributeSetId($attributeSetId);
            $html = $conditionsBlock->render($element);

            return $result->setData([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while loading conditions.') . ' ' . $e->getMessage()
            ]);
        }
    }
}