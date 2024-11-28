<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magezon\Blog\Helper\Data;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Data $dataHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $route = $this->dataHelper->getRoute();
        $identifier = trim($this->getRequest()->getPathInfo(), '/');
        $paths = explode("/", $identifier);
        if ($route != Data::ROUTER && $paths[0] == Data::ROUTER) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $resultPage = $this->resultPageFactory->create();
        $pageLayout = $this->dataHelper->getConfig('latest_page/page_layout');
        $resultPage->getConfig()->setPageLayout($pageLayout);
        return $resultPage;
    }
}
