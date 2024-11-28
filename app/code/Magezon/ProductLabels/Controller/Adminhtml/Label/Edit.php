<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductLabels::label_save';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry     = $registry;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::marketing')
            ->addBreadcrumb(__('ProductLabels'), __('ProductLabels'))
            ->addBreadcrumb(__('Manage Labels'), __('Manage Labels'));
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('label_id');
        $model = $this->_objectManager->create('Magezon\ProductLabels\Model\Label');

            
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This label no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $model->getConditions()->setFormName('productlabels_label_form');
            $model->getConditions()->setJsFormObject(
                $model->getConditionsFieldSetId($model->getConditions()->getFormName())
            );
            $model->getActions()->setFormName('productlabels_label_form');
            $model->getActions()->setJsFormObject(
                $model->getActionsFieldSetId($model->getActions()->getFormName())
            );
        }
        $this->_coreRegistry->register('mgz_conditions_model', $model);
        $this->_coreRegistry->register('mgz_conditions_form_name', 'productlabels_label_form');
        $this->getRequest()->setParam('mgz_conditions_grid_id', rand());
        $this->_coreRegistry->register('productlabels_label', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Label') : __('New Label'),
            $id ? __('Edit Label') : __('New Label')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Labels'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Label'));

        return $resultPage;
    }
}
