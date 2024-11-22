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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Controller\Adminhtml\File;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magezon\ProductAttachments\Helper\Data;
use Magezon\ProductAttachments\Model\File;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::file';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Data $dataHelper
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Edit file
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $this->messageManager->addNotice(
            $this->dataHelper->getMaxUploadSizeMessage()
        );
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('file_id');
        $model = $this->_objectManager->create(File::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This file no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $model->getConditions()->setFormName('productattachments_file_form');
            $model->getConditions()->setJsFormObject(
                $model->getConditionsFieldSetId($model->getConditions()->getFormName())
            );
            $model->getActions()->setFormName('productattachments_file_form');
            $model->getActions()->setJsFormObject(
                $model->getActionsFieldSetId($model->getActions()->getFormName())
            );
        }
        $this->coreRegistry->register('mgz_conditions_model', $model);
        $this->coreRegistry->register('mgz_conditions_form_name', 'productattachments_file_form');
        $this->coreRegistry->register('productattachments_file', $model);
        $this->getRequest()->setParam('mgz_conditions_grid_id', rand());

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magezon_Core::extensions')
            ->addBreadcrumb(
                $id ? __('Edit File') : __('New File'),
                $id ? __('Edit File') : __('New File')
            );
        $resultPage->getConfig()->getTitle()->prepend(__('File'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getLabel() : __('New File'));
        return $resultPage;
    }
}
