<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_SmileCustomEntityWidget
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Controller\Adminhtml\Set;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class Chooser extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var RawFactory
     */
    protected RawFactory $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected LayoutFactory $layoutFactory;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);

        $layout = $this->layoutFactory->create();
        $customEntitySetGrid = $layout->createBlock(
            \Artbambou\SmileCustomEntityWidget\Block\Adminhtml\Set\Chooser::class,
            '',
            [
                'data' => [
                    'id' => $uniqId,
                    'use_massaction' => $massAction
                ]
            ]
        );

        $html = $customEntitySetGrid->toHtml();

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($html);
    }
}