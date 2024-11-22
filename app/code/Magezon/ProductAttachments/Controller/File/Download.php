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

namespace Magezon\ProductAttachments\Controller\File;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\ProductAttachments\Model\ReportFactory;
use Magezon\ProductAttachments\Model\FileUploader;
use Magezon\ProductAttachments\Model\File;
use Magezon\ProductAttachments\Model\ResourceModel\File\Collection;

class Download extends Action
{
    /**
     * @var ReportFactory
     */
    protected $reportFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Magento\Downloadable\Helper\Download
     */
    protected $helper;

    /**
     * Download constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ReportFactory $reportFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Downloadable\Helper\Download $helper,
        StoreManagerInterface $storeManager,
        ReportFactory $reportFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->reportFactory = $reportFactory;
        $this->helper = $helper;
    }

    public function execute()
    {
        $redirectTo = $this->_redirect->getRefererUrl();
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectTo);
        $id = $this->getRequest()->getParam('id');
        $customerId = $this->customerSession->getCustomerId();
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->_objectManager->create(
            Collection::class
        );
        $collection->prepareCollection()
            ->addFieldToFilter('file_hash', $id);
        $collection->addTotalDownloads();
        $attachment = $collection->getFirstItem();
        if (!$attachment->getId()) {
            $this->messageManager->addNotice(__("We can't find the file you download."));
            return $this->_redirect('noroute');
        }
        $numberOfDownloadsUsed = $attachment->getTotalDownloads();
        $limitDownload = $attachment->getDownloadLimit();
        if (($limitDownload && ($numberOfDownloadsUsed < $limitDownload)) || !$limitDownload) {
            $resource = $resourceType = '';
            if ($attachment->getFileType() == File::TYPE_FILE) {
                $resourceType = 'file';
                $resource = FileUploader::BASE_PATH . $attachment->getName();
                $fileName = $attachment->getDownloadName();
            } else {
                $resource = $attachment->getLink();
                $resourceType = 'url';
                $fileName = $attachment->getDownloadName();
            }
            try {
                $this->helper->setResource($resource, $resourceType);
                $contentType = $this->helper->getContentType();
                $this->processDownload($contentType, $fileName);
                if (explode('/', $contentType)[0] == 'video' && $_SERVER['HTTP_SEC_FETCH_DEST'] == 'document') {
                    return;
                }
                $dataReport = [
                    'file_id' => $attachment->getId(),
                    'file_name' => $attachment->getLabel(),
                    'customer_id' => $customerId,
                    'store_id' => $storeId,
                ];
                $this->saveReportDownload($dataReport);
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        } else {
            $this->messageManager->addNotice(__('The file has expired.'));
        }
        return $resultRedirect;
    }

    /**
     * @param $data
     * @return object
     */
    public function saveReportDownload($data)
    {
        $report = $this->reportFactory->create();
        $report->setData($data);
        $report->save();
        return $report;
    }

    /**
     * Download process
     *
     * @param string $resource
     * @param string $resourceType
     * @param null $fileName
     * @return void
     */
    public function processDownload($contentType, $fileName)
    {
        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $this->helper->getFileSize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $this->helper->getContentDisposition()) {
            $this->getResponse()
                ->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $this->helper->output();
    }
}
