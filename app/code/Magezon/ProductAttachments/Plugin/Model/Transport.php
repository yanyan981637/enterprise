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

namespace Magezon\ProductAttachments\Plugin\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mail\TransportInterface as Subject;
use Magento\Framework\Registry;
use Magezon\ProductAttachments\Helper\Data;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory as FileCollectionFactory;
use Zend\Mime\Message;
use Zend\Mime\Part;
use Zend_Mime;

class Transport
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FileCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var array
     */
    protected $fileCollection;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @var ResourceConnection
     */
    protected $connection;

    /**
     * Transport constructor.
     * @param Registry $registry
     * @param Data $helper
     * @param ResourceConnection $resource
     * @param FileCollectionFactory $fileCollectionFactory
     */
    public function __construct(
        Registry $registry,
        Data $helper,
        ResourceConnection $resource,
        FileCollectionFactory $fileCollectionFactory
    ) {
        $this->connection = $resource;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->fileCollectionFactory = $fileCollectionFactory;
    }

    public function beforeSendMessage(
        Subject $subject
    ) {
        $type = $this->registry->registry('mgzatm_type');
        if ($this->helper->isEnabled() && $this->helper->isEmailOrder() && $type) {
            $files = $this->getFiles();
            $message = $subject->getMessage();
            if (is_array($files)) {
                foreach ($files as $sku => $items) {
                    foreach ($items as $file) {
                        try {
                            $this->prepareMessage(
                                $message,
                                file_get_contents($file->getAbsolutePathFile()),
                                $sku . '_' . $file->getLabel(),
                                $file->getMimeType()
                            );
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }
    }

    public function prepareMessage($message, $content, $name, $type)
    {
        if ($type == null) {
            $type = 'application/pdf';
        }
        $this->setParts($message->getBody()->getParts());
        $this->createAttachment(
            $content,
            $type,
            Zend_Mime::DISPOSITION_ATTACHMENT,
            Zend_Mime::ENCODING_BASE64,
            $name
        );
        $parts = $this->getParts();
        $mimeMessage = new Message();
        $mimeMessage->setParts($parts);
        $message->setBody($mimeMessage);
    }

    public function createAttachment(
        $body,
        $mimeType,
        $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding = Zend_Mime::ENCODING_BASE64,
        $filename = null
    ) {
        $mp = new Part($body);
        $mp->encoding = $encoding;
        $mp->type = $mimeType;
        $mp->disposition = $disposition;
        $mp->filename = $filename;
        $this->_addAttachment($mp);
        return $mp;
    }

    /**
     * Adds an existing attachment to the mail message
     *
     * @param Zend_Mime_Part $attachment
     * @return Zend_Mail Provides fluent interface
     */
    public function _addAttachment($attachment)
    {
        $this->addPart($attachment);
        return $this;
    }

    /**
     * @param Zend_Mime_Part $part
     */
    public function addPart($part)
    {
        $this->_parts[] = $part;
    }

    /**
     * @return array
     */
    public function getParts()
    {
        return $this->_parts;
    }

    /**
     * @param array $parts
     */
    public function setParts($parts)
    {
        $this->_parts = $parts;
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('mgzatm_source');
    }

    /**
     * @return \Magezon\ProductAttachments\Model\ResourceModel\File\Collection
     */
    public function getFileCollection()
    {
        if ($this->fileCollection == null) {
            $collection = $this->fileCollectionFactory->create();
            $collection->prepareCollection()
                ->getSelect()
                ->joinLeft(
                    ['mpap' => $collection->getResource()->getTable('mgz_product_attachments_product')],
                    'main_table.file_id = mpap.file_id',
                    ['product_id']
                )->where(
                'mpap.product_id IN (?)',
                $this->getProductIds()
            )->group(
                'main_table.file_id'
            );
            $collection->addFieldToFilter('attach_email', 1)
                ->setOrder('main_table.priority', 'ASC');
            if ($this->getOrder()->getStatus() !== 'complete') {
                $collection->addFieldToFilter('is_buyer', 0);
            }
            $this->fileCollection = $collection;
        }
        return $this->fileCollection;
    }

    /**
     * Get Product id by order
     *
     * @return array
     */
    public function getProductIds()
    {
        if ($this->productIds == null) {
            $items = $this->getOrder()->getAllVisibleItems();
            $productIds = [];
            foreach ($items as $item) {
                $productIds[$item->getSku()] = $item->getProductId();
            }
            $this->productIds = $productIds;
        }
        return $this->productIds;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFilesRelations()
    {
        $connection = $this->connection->getConnection();
        $select = $connection->select()->from($connection->getTableName('mgz_product_attachments_product'))
            ->where('product_id IN (?)', $this->getProductIds())
            ->where('store_id = ?', $this->getOrder()->getStoreId());
        return (array) $connection->fetchAll($select);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFiles()
    {
        $filesRelations = $this->getFilesRelations();
        $files = [];
        foreach ($this->getProductIds() as $sku => $productId) {
            $result = [];
            foreach ($filesRelations as $_re) {
                if ($_re['product_id'] == $productId) {
                    if ($items = $this->getFileCollection()->getItemByColumnValue('file_id', $_re['file_id'])) {
                        $result[] = $items;
                    }
                }
            }
            $files[$sku] = $result;
        }
        return $files;
    }
}
