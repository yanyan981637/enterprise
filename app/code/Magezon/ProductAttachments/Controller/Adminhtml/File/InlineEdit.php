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

use Magento\Backend\App\Action\Context;
use Magezon\ProductAttachments\Api\FileRepositoryInterface as FileRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magezon\ProductAttachments\Api\Data\FileInterface;
use Magezon\ProductAttachments\Model\File;

class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::file_save';

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magezon\ProductAttachments\Model\FileUploader
     */
    private $fileUploader;

    /**
     * @param Context $context
     * @param FileRepository $fileRepository
     * @param \Magezon\ProductAttachments\Model\FileUploader $fileUploader
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        FileRepository $fileRepository,
        \Magezon\ProductAttachments\Model\FileUploader $fileUploader,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->fileRepository = $fileRepository;
        $this->fileUploader = $fileUploader;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $fileId) {
                    /** @var File $file */
                    $file = $this->fileRepository->getById($fileId);
                    try {
                        $file->setData(array_merge($file->getData(), $postItems[$fileId]));
                        if ($file->getType() == 'file') {
                            if (isset($postItems[$fileId]['file_name'][0]['name'])) {
                                $file->setFileName($postItems[$fileId]['file_name'][0]['name']);
                                if (isset($postItems[$fileId]['file_name'][0]['tmp_name'])) {
                                    $this->fileUploader->moveFileFromTmp($postItems[$fileId]['file_name'][0]['name'], null);
                                }
                            } else {
                                $file->setFileName(pathinfo($file->getFileName(),PATHINFO_BASENAME));
                            }
                            $file->setLink('');
                        } else {
                            $file->setFileName('');
                        }
                        $this->fileRepository->save($file);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithFileId(
                            $file,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add block title to error message
     *
     * @param FileInterface $file
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithFileId(FileInterface $file, $errorText)
    {
        return '[File ID: ' . $file->getId() . '] ' . $errorText;
    }
}
