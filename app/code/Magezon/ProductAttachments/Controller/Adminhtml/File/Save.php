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
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magezon\ProductAttachments\Model\File;
use Magezon\ProductAttachments\Model\FileFactory;
use Magezon\ProductAttachments\Model\FileUploader;
use Magezon\ProductAttachments\Model\Processor;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::file_save';

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var FileUploader
     */
    protected $fileUploader;

    /**
     * Save constructor.
     * @param Context $context
     * @param FileUploader $fileUploader
     * @param FileFactory $fileFactory
     * @param Processor $processor
     */
    public function __construct(
        Context $context,
        FileUploader $fileUploader,
        FileFactory $fileFactory,
        Processor $processor
    ) {
        parent::__construct($context);
        $this->fileUploader = $fileUploader;
        $this->fileFactory = $fileFactory;
        $this->processor = $processor;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $file = $this->fileFactory->create();
                $redirectBack = $this->getRequest()->getParam('back', false);
                $id = $this->getRequest()->getParam('file_id');
                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }
                unset($data['conditions_serialized']);
                unset($data['actions_serialized']);
                $name = '';
                if ($id) {
                    $file->load($id);
                    if (!$file->getId()) {
                        throw new LocalizedException(__('This file no longer exists.'));
                    }
                    $name = $file->getName();
                }
                $file->loadPost($data);
                if ($file->getFileType() == File::TYPE_FILE) {
                    $fileNameUpload = $file->getData('file_upload/0/name');
                    if ($name && $name != $fileNameUpload) {
                        $this->fileUploader->deleteImage($name);
                    }
                    if (!$name || ($name && $name != $fileNameUpload)) {
                        $fileName = $this->fileUploader->moveFileFromTmp($fileNameUpload, null);
                        $file->setName($fileName);
                    }
                }
                $file->setFileExtension($file->getData('file_data/file_extension'));
                $file->save();
                $id = $file->getId();
                $this->messageManager->addSuccessMessage(__('You saved the File.'));
                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->processor->process($file);
                    return $resultRedirect->setPath('*/*/edit', ['file_id' => $id]);
                }
                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }
                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/');
                }
                return $resultRedirect->setPath('*/*/edit', ['file_id' => $id]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the file.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
