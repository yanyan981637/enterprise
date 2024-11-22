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

namespace Magezon\Blog\Block\Sidebar;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Block\ListPost;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Post;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class PopularPosts extends Template
{
	/**
	 * @var \Magento\Framework\App\Http\Context
	 */
	protected $httpContext;

	/**
	 * @var Data
	 */
	protected $dataHelper;

	/**
	 * @var CollectionFactory
	 */
	protected $collectionFactory;

	/**
	 * @var Collection
	 */
	protected $_collection;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
	public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->httpContext       = $httpContext;
		$this->dataHelper        = $dataHelper;
		$this->collectionFactory = $collectionFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags'     => [Post::CACHE_TAG]
        ]);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cache = [
            'MGZ_BLOG_SIDEBAR_POPULARPOSTS',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
        ];
        return $cache;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
    	if ($this->_collection === NULL) {
    		$numberOfPosts = (int)$this->dataHelper->getConfig('sidebar/tabs/popular_posts/number_of_posts');
			$collection = $this->collectionFactory->create();
			$collection->prepareCollection();
			$collection->addAuthorToCollection();
			$collection->addTotalComments();
			$collection->setOrder('total_views', 'DESC');
			$collection->setPageSize($numberOfPosts);
			$this->_collection = $collection;
		}
		return $this->_collection;
    }

    /**
     * @return string
     */
	public function getPostListHtml()
	{
		$collection = $this->getCollection();
		$block = $this->getLayout()->createBlock(ListPost::class);
		$block->setCollection($collection);
		$block->setTemplate('Magezon_Blog::post/list2.phtml');
		$block->setShowAuthor(false);
		$block->setShowComment(false);
		$block->setShowCategory(false);
		return $block->toHtml();
	}
}