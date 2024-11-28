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

namespace Magezon\Blog\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magezon\Blog\Helper\Data;

class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper        = $dataHelper;
        $this->_coreRegistry     = $registry;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $post = $this->_coreRegistry->registry('current_post');
        $pageLayout = $this->dataHelper->getConfig('post_page/page_layout');
        if ($post['page_layout'] == 'default') {
            $post['page_layout'] = $pageLayout;
        }
        $resultPage->getConfig()->setPageLayout($post->getPageLayout());
        return $resultPage;
    }
}
