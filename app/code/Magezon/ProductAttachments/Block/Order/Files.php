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

namespace Magezon\ProductAttachments\Block\Order;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magezon\ProductAttachments\Block\ListAttachment;
use Magezon\ProductAttachments\Helper\Data;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory;

class Files extends Template
{
    protected $_template = 'Magezon_ProductAttachments::order/files.phtml';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var array
     */
    protected $fileCollection;

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    protected $dataHelper;

    /**
     * Files constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceConnection $resource
     * @param Data $dataHelper
     * @param CollectionFactory $fileCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceConnection $resource,
        Data $dataHelper,
        CollectionFactory $fileCollectionFactory
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->registry = $registry;
        $this->dataHelper = $dataHelper;
        $this->fileCollectionFactory = $fileCollectionFactory;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
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
            $collection->addTotalDownloads();
            $collection->addFieldToFilter('attach_order', 1)
                ->setOrder('main_table.priority', 'ASC');
            if ($this->getOrder()->getStatus() !== 'complete') {
                $collection->addFieldToFilter('is_buyer', 0);
            }
            $this->fileCollection = $collection;
        }
        return $this->fileCollection;
    }

    /**
     * @param $files
     * @return string
     */
    public function getListFileHtml($files)
    {
        $block = $this->getLayout()->createBlock(
            ListAttachment::class
        )->setFiles($files);
        return $block->toHtml();
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->dataHelper->isOrderPage()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get Product id by order
     *
     * @return array
     */
    public function getProductIds()
    {
        if ($this->productIds == null) {
            $productIds = [];
            foreach ($this->getOrder()->getAllVisibleItems() as $item) {
                $productIds[$item->getItemId()] = $item->getProductId();
            }
            $this->productIds = $productIds;
        }
        return $this->productIds;
    }

    /**
     * @return array
     */
    public function getFilesRelations()
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from($connection->getTableName('mgz_product_attachments_product'))
            ->where('product_id IN (?)', $this->getProductIds())
            ->where('store_id = ?', $this->getOrder()->getStoreId());
        return (array)$connection->fetchAll($select);
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        $filesRelations = $this->getFilesRelations();
        $files = [];
        foreach ($this->getProductIds() as $itemId => $productId) {
            $result = [];
            foreach ($filesRelations as $_re) {
                if ($_re['product_id'] == $productId) {
                    if ($items = $this->getFileCollection()->getItemByColumnValue('file_id', $_re['file_id'])) {
                        $result[] = $items;
                    }
                }
            }
            $files[$itemId] = $result;
        }
        return $files;
    }
}
