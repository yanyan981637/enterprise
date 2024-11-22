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

namespace Magezon\Blog\Block\Search;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Block\ListPost;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class Result extends Template
{
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
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
	public function __construct(
        Context $context,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $this->pageConfig->getTitle()->set($this->getSearchTitle());
        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _addBreadcrumbs()
    {
		$breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
		if ($breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link'  => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $title = $this->dataHelper->getBlogTitle();
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => $title,
                    'title' => $title,
                    'link'  => $this->dataHelper->getBlogUrl()
                ]
            );
            $title = $this->getSearchTitle();
            if ($title) $breadcrumbsBlock->addCrumb('search', ['label' => $title, 'title' => $title]);
        }
    }

    /**
     * @return string
     */
    public function getSearchTitle()
    {
        return __("Search Results for: '%1'", $this->getSearchQuery());
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return trim((string)$this->getRequest()->getParam('s'));
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === NULL) {
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection();
            $collection->addAuthorToCollection();
            $collection->addTotalComments();
            $collection->addFieldToFilter('title', ['like' => '%' . $this->getSearchQuery() . '%']);
            $collection->setOrder('publish_date', 'DESC');
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
        $block->setShowPager(true);
        $block->setShowAuthor($this->dataHelper->getConfig('post_listing/post_author'));
        $block->setShowDate($this->dataHelper->getConfig('post_listing/post_date'));
        $block->setShowCategory($this->dataHelper->getConfig('post_listing/post_cats'));
        $block->setShowComment($this->dataHelper->getConfig('post_listing/post_comments'));
        $block->setShowView($this->dataHelper->getConfig('post_listing/post_views'));
        $block->setReadTime($this->dataHelper->getConfig('post_listing/post_read_time'));
        $block->setNoResultText(__('Sorry, but nothing matched your search criteria. Please try again with some different keywords.'));
		$data['list_layout'] = $this->dataHelper->getConfig('search_page/layout');
		$data['grid_col'] = $this->dataHelper->getConfig('search_page/grid_col');
		$block->addData($data);
		return $block->toHtml();
	}
}
