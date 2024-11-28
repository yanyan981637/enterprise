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

namespace Magezon\Blog\Controller\Adminhtml\Author;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_Blog::author_save';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry     = $registry;
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::content_elements')
            ->addBreadcrumb(__('Blog'), __('Blog'))
            ->addBreadcrumb(__('Manage Authors'), __('Manage Authors'));
        return $resultPage;
    }

    /**
     * @return Page|ResponseInterface|\Magento\Framework\Controller\Result\Redirect|ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('author_id');
        $model = $this->_objectManager->create('Magezon\Blog\Model\Author');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This author no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_coreRegistry->register('current_author', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Author') : __('New Author'),
            $id ? __('Edit Author') : __('New Author')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Authors'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getPublicName() : __('New Author'));

        return $resultPage;
    }
}
