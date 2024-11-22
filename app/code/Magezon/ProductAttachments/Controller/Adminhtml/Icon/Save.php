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

namespace Magezon\ProductAttachments\Controller\Adminhtml\Icon;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magezon\ProductAttachments\Model\IconFactory;
use Magezon\ProductAttachments\Model\IconUploader;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::icon_save';

    /**
     * @var iconFactory
     */
    protected $iconFactory;

    /**
     * @var
     */
    protected $iconUploader;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Save constructor.
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param IconUploader $iconUploader
     * @param IconFactory $iconFactory
     * @param IconCollectionFactory $iconCollectionFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        IconUploader $iconUploader,
        IconFactory $iconFactory
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->iconUploader = $iconUploader;
        $this->iconFactory = $iconFactory;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $redirectBack = $this->getRequest()->getParam('back', false);
                $listExtension = [];
                if (isset($data['dynamic_rows'])) {
                    foreach ($data['dynamic_rows'] as $value) {
                        $listExtension[] = $value['extension'];
                    }
                }
                $model = $this->iconFactory->create();
                $id = $this->getRequest()->getParam('icon_id');
                $name = '';
                if ($id) {
                    $model->load($id);
                    if (!$model->getId()) {
                        throw new LocalizedException(__('This icon no longer exists.'));
                    }
                    $name = $model->getFileName();
                }
                $model->setData($data);
                $fileNameUpload = $model->getData('icon/0/name');
                if ($name && $name != $fileNameUpload) {
                    $this->iconUploader->deleteImage($name);
                }
                if (!$name || ($name && $name != $fileNameUpload)) {
                    $fileName = $this->iconUploader->moveFileFromTmp($fileNameUpload, null);
                    $model->setFileName($fileName);
                }
                $model->setFileType(implode(',', $listExtension));
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the icon.'));
                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }
                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/');
                }

                $this->dataPersistor->set('productattachments_icon', $data);
                return $resultRedirect->setPath('*/*/edit', ['icon_id' => $model->getId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the icon.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
