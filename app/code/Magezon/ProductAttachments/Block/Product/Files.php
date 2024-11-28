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

namespace Magezon\ProductAttachments\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template;
use Magezon\ProductAttachments\Block\ListAttachment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\SessionFactory;
use Magezon\ProductAttachments\Helper\Data;
use Magezon\ProductAttachments\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory as FileCollectionFactory;

class Files extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magezon_ProductAttachments::product/files.phtml';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FileCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \Magezon\ProductAttachments\Model\ResourceModel\File\Collection
     */
    protected $fileCollection;

    /**
     * @var \Magezon\ProductAttachments\Model\ResourceModel\Category\Collection
     */
    protected $categoryCollection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SessionFactory
     */
    private $session;

    /**
     * Files constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     * @param FileCollectionFactory $fileCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        SessionFactory $sessionFactory,
        Data $dataHelper,
        FileCollectionFactory $fileCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->session = $sessionFactory->create();
        $this->dataHelper = $dataHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->fileCollectionFactory = $fileCollectionFactory;
    }

    /**
     * Get current product
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
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
                    []
                )
                ->where('mpap.product_id = ?', $this->getCurrentProduct()->getId())
                ->group([
                    'main_table.file_id'
                ]);
            $collection->addTotalDownloads();
            $collection->setOrder('main_table.priority', 'ASC');
            if (!$this->session->isLoggedIn()
                || ($this->session->isLoggedIn() && !$this->isPurchased($this->session->getCustomerId()))
            ) {
                $collection->addFieldToFilter('is_buyer', 0);
            }
            $this->fileCollection = $collection;
        }
        return $this->fileCollection;
    }

    /**
     * Get category file collection
     * @return \Magezon\ProductAttachments\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection()
    {
        if ($this->categoryCollection == null) {
            $catIds = array_unique($this->getFileCollection()->getColumnValues('category_id'));
            $collection = $this->categoryCollectionFactory->create();
            $collection->addIsActiveFilter();
            $collection->addFieldToFilter('category_id', ['in' => $catIds]);
            $this->categoryCollection = $collection;
        }
        return $this->categoryCollection;
    }

    /**
     * @param $files
     * @param int $total
     * @return string
     */
    public function getListFileHtml($files, $total = 0)
    {
        $block = $this->getLayout()->createBlock(ListAttachment::class);
        $items = [];
        $count = 0;
        foreach ($files as $file) {
            if ($total && $count < $total || !$total) {
                $items[] = $file;
            }
            $count++;
        }
        $block->setFiles($items);
        return $block->toHtml();
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $blockType = $this->getData('block_type');
        if (($blockType != $this->dataHelper->getPosition() && $blockType)
            || !$this->getFileCollection()->count()
            || ($this->dataHelper->isEnabledFileCategory() && !$this->getCategoryCollection()->count())
            ) {
            return;
        }
        $this->setTitle(
            $this->dataHelper->getTitle() ?
            $this->dataHelper->getTitle().' ('.$this->getFileCollection()->count().')'
            : __('Attachments').' ('.$this->getFileCollection()->count().')'
        );
        return parent::_toHtml();
    }

    /**
     * Check product purchased
     *
     * @param $storeId
     * @param $customerId
     * @param $productId
     */
    public function isPurchased($customerId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from([
            'so' => $connection->getTableName('sales_order')
        ])
        ->join(
            ['soi' => $connection->getTableName('sales_order_item')],
            'so.entity_id = soi.order_id',
            []
        )
            ->where('so.store_id = ?', $this->storeManager->getStore()->getId())
            ->where('so.customer_id = ?', $customerId)
            ->where('so.status = ?', 'complete')
            ->where('soi.product_id = ?', $this->getCurrentProduct()->getId());
        return count($connection->fetchAll($select));
    }
}
