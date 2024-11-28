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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Block\Product;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magezon\Blog\Block\ListPost;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class RelatedPosts extends Template
{
    /**
     * @var Registry
     */
	protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $urlHelper;

    /**
     * @var Collection
     */
	protected $_collection;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
    	Context $context,
        Registry $registry,
        CollectionFactory $collectionFactory,
        Data $dataHelper,
    	array $data = []
    ) {
    	parent::__construct($context, $data);
        $this->registry          = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper        = $dataHelper;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->dataHelper->getConfig('product_page/related_posts/enabled')
            || !$this->getCollection()->count()
        ){
            return;
        }
        return parent::toHtml();
    }

    /**
     * @return Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === NULL) {
            $product = $this->getCurrentProduct();
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection();
            $collection->getSelect()->joinLeft(
                ['mbpp' => $collection->getResource()->getTable('mgz_blog_post_product')],
                'main_table.post_id = mbpp.post_id',
                []
            )->where('mbpp.product_id = ?', $product->getId())->group('main_table.post_id');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return string
     */
    public function getPostListHtml()
    {
        $numberOfPosts = (int)$this->dataHelper->getConfig('product_page/related_posts/number_of_posts');
        $post = $this->getCurrentPost();
        $collection = $this->getCollection();
        $collection->setPageSize($numberOfPosts);
        $block = $this->getLayout()->createBlock(ListPost::class);
        $block->setTemplate('Magezon_Blog::post/slider.phtml');
        $block->setCollection($collection);
        $block->setShowAuthor(false);
        $block->setShowCategory(false);
        $block->setShowComment(false);
        $block->setShowExcerpt(false);
        $block->setShowView(false);
        $block->setReadTime(false);
        return $block->toHtml();
    }
}