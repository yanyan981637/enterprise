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

namespace Magezon\Blog\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Config\Source\PostsSortBy;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class LatestPosts extends Template
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
     * Sort By Publish date
     */
    const POSTS_SORT_FIELD_BY_PUBLISH_DATE = 'publish_date';

    /**
     * Sort By Position
     */
    const POSTS_SORT_FIELD_BY_POSITION = 'position';

    /**
     * Sort By Title
     */
    const POSTS_SORT_FIELD_BY_TITLE = 'title';

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
        $title           = $this->dataHelper->getBlogTitle();
        $metaTitle       = $this->dataHelper->getConfig('latest_page/meta_title');
        $metaKeywords    = $this->dataHelper->getConfig('latest_page/meta_keywords');
        $metaDescription = $this->dataHelper->getConfig('latest_page/meta_description');

        $this->pageConfig->getTitle()->set($metaTitle ? $metaTitle : $title);
        $this->pageConfig->setKeywords($metaKeywords);
        $this->pageConfig->setDescription($metaDescription);
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) $pageMainTitle->setPageTitle($title);

        if ($this->dataHelper->isEnabledLatestPostsRss()) {
            $store = $this->_storeManager->getStore();
            $title = __('Latest Posts from %1', $store->getFrontendName());
            $this->pageConfig->addRemotePageAsset(
                $this->dataHelper->getLatestPostsRssLink(),
                'blog_rss',
                [
                    'attributes' => [
                        'rel'   => 'alternate',
                        'type'  => 'application/rss+xml',
                        'title' => $title
                    ]
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve collection order field
     *
     * @return string
     */
    public function getCollectionOrderField()
    {
        $postsSortBy = $this->dataHelper->getConfig('latest_page/posts_sort_by');
        switch ($postsSortBy) {
            case PostsSortBy::POSITION:
                return self::POSTS_SORT_FIELD_BY_POSITION;
            case PostsSortBy::TITLE:
                return self::POSTS_SORT_FIELD_BY_TITLE;
            default:
                return self::POSTS_SORT_FIELD_BY_PUBLISH_DATE;
        }
    }

    /**
     * Prepare breadcrumbs
     *
     * @throws LocalizedException
     * @return void
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
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $title = $this->dataHelper->getBlogTitle();
            if ($title) $breadcrumbsBlock->addCrumb('blog', ['label' => $title, 'title' => $title]);
        }
    }

    /**
     * @return string
     */
    public function getPostListHtml()
    {
        $showComment  = $this->dataHelper->getConfig('post_listing/post_comments');
        $showAuthor   = $this->dataHelper->getConfig('post_listing/post_author');
        $showCategory = $this->dataHelper->getConfig('post_listing/post_cats');
        $postsPerPage = $this->dataHelper->getConfig('latest_page/posts_per_page');
        $collection = $this->collectionFactory->create();
        $collection->prepareCollection();
        if ($showAuthor) $collection->addAuthorToCollection();
        if ($showComment) $collection->addTotalComments();
        $collection->setOrder('pinned', 'DESC');
        if($this->getCollectionOrderField() == self::POSTS_SORT_FIELD_BY_PUBLISH_DATE) {
            $collection->setOrder($this->getCollectionOrderField(), 'DESC');
        }else{
            $collection->setOrder($this->getCollectionOrderField(), 'ASC');
        }
        $block = $this->getLayout()->createBlock(ListPost::class);
        $block->setPostsPerPage($postsPerPage);
        $block->setShowPager(true);
        $block->setCollection($collection);
        $block->setShowAuthor($showAuthor);
        $block->setShowDate($this->dataHelper->getConfig('post_listing/post_date'));
        $block->setShowCategory($showCategory);
        $block->setShowComment($showComment);
        $block->setShowView($this->dataHelper->getConfig('post_listing/post_views'));
        $block->setReadTime($this->dataHelper->getConfig('post_listing/post_read_time'));
        $data['list_layout'] = $this->dataHelper->getConfig('latest_page/layout');
        $data['grid_col'] = $this->dataHelper->getConfig('latest_page/grid_col');
        $block->addData($data);
        return $block->toHtml();
    }
}
