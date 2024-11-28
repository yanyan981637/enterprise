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

namespace Magezon\ProductAttachments\Block\Widget;

use Magento\Catalog\Block\Product\Context;
use Magezon\ProductAttachments\Block\ListAttachment;
use Magezon\ProductAttachments\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory as FileCollectionFactory;

class Files extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Magezon_ProductAttachments::widget/files.phtml';

    /**
     * @var FileCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magezon\ProductAttachments\Model\ResourceModel\File\Collection
     */
    protected $fileCollection;

    /**
     * @var \Magezon\ProductAttachments\Model\ResourceModel\Category\Collection
     */
    protected $categoryCollection;

    /**
     * Files constructor.
     * @param Context $context
     * @param FileCollectionFactory $fileCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Context $context,
        FileCollectionFactory $fileCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($context);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->fileCollectionFactory = $fileCollectionFactory;
    }

    /**
     * @return \Magezon\ProductAttachments\Model\ResourceModel\File\Collection
     */
    public function getFileCollection()
    {
        if ($this->fileCollection == null) {
            $collection = $this->fileCollectionFactory->create();
            $collection->addFieldToFilter('category_id', ['in' => $this->getData('category_file')]);
            if (!$this->getData('option')) {
                $collection->addTotalDownloads();
                $collection->setOrder('total_downloads', 'DESC');
            }
            $collection->setPageSize($this->getData('page_size'));
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
     * @return bool
     */
    public function isCategory()
    {
       return ($this->getData('list_display') == 'group') ? true : false;
    }
}
