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

namespace Magezon\Blog\Block\Tag;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Block\ListPost;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Tag;

class View extends Template
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $dataHelper
     * @param array $data
     */
	public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->dataHelper    = $dataHelper;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $tag = $this->getCurrentTag();
        if($tag) {
            $this->pageConfig->getTitle()->set($tag->getMetaTitle() ? $tag->getMetaTitle() : $tag->getTitle());
            $this->pageConfig->setKeywords($tag->getMetaKeywords());
            $this->pageConfig->setDescription($tag->getMetaDescription());
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) $pageMainTitle->setPageTitle(__('Tag Archives: %1', $tag->getTitle()));
            $this->pageConfig->addRemotePageAsset(
                $tag->getCanonicalUrl() ? $tag->getCanonicalUrl() : $tag->getUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        return parent::_prepareLayout();
    }

    /**
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
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $title = $this->dataHelper->getBlogTitle();
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => $title,
                    'title' => $title,
                    'link' => $this->dataHelper->getBlogUrl()
                ]
            );
            $tag = $this->getCurrentTag();
            if ($tag && $this->dataHelper->enableTagPage()) {
                if ($title) {
                    $breadcrumbsBlock->addCrumb('tag',
                        [
                            'label' => __('Tag Archives: %1', $tag->getTitle()),
                            'title' => __('Tag Archives: %1', $tag->getTitle())
                        ]);
                }
            }
        }
    }

    /**
     * Retrieve current tag model object
     *
     * @return Tag
     */
    public function getCurrentTag()
    {
        return $this->_coreRegistry->registry('current_tag');
    }

    /**
     * @return string
     */
	public function getPostListHtml()
	{
		$tag = $this->getCurrentTag();
		$collection = $tag->getPostCollection();
		$block = $this->getLayout()->createBlock(ListPost::class);
		$block->setCollection($collection);
        $block->setShowPager(true);
        $block->setShowAuthor($this->dataHelper->getConfig('post_listing/post_author'));
        $block->setShowDate($this->dataHelper->getConfig('post_listing/post_date'));
        $block->setShowCategory($this->dataHelper->getConfig('post_listing/post_cats'));
        $block->setShowComment($this->dataHelper->getConfig('post_listing/post_comments'));
        $block->setShowView($this->dataHelper->getConfig('post_listing/post_views'));
        $block->setReadTime($this->dataHelper->getConfig('post_listing/post_read_time'));
		$data['list_layout'] = $this->dataHelper->getConfig('tag_page/layout');
		$data['grid_col']    = $this->dataHelper->getConfig('tag_page/grid_col');
		$block->addData($data);
		return $block->toHtml();
	}
}
