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

namespace Magezon\Blog\Block\Author;
use Magezon\Blog\Block\ListPost;
use Magezon\Blog\Model\Author;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;

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
        $author = $this->getCurrentAuthor();
        if($author) {
            $this->pageConfig->getTitle()
                ->set($author->getMetaTitle() ? $author->getMetaTitle() : $author->getPublicName());
            $this->pageConfig->setKeywords($author->getMetaKeywords());
            $this->pageConfig->setDescription($author->getMetaDescription());
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) $pageMainTitle->setPageTitle($author->getPublicName());
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @throws \Magento\Framework\Exception\LocalizedException
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
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => $title,
                    'title' => $title,
                    'link' => $this->dataHelper->getBlogUrl()
                ]
            );
            $author = $this->getCurrentAuthor();
            if ($author && $title) {
                $breadcrumbsBlock->addCrumb('author',
                    ['label' => $author->getPublicName(), 'title' => $author->getPublicName()]
                );
            }
        }
    }

    /**
     * Retrieve current author model object
     *
     * @return Author
     */
    public function getCurrentAuthor()
    {
        return $this->_coreRegistry->registry('current_author');
    }

    /**
     * @return string
     */
	public function getPostListHtml()
	{
		$author = $this->getCurrentAuthor();
		$collection = $author->getPostCollection();
		$block = $this->getLayout()->createBlock(ListPost::class);
		$block->setCollection($collection);
        $block->setShowPager(true);
        $block->setShowAuthor($this->dataHelper->getConfig('post_listing/post_author'));
        $block->setShowDate($this->dataHelper->getConfig('post_listing/post_date'));
        $block->setShowCategory($this->dataHelper->getConfig('post_listing/post_cats'));
        $block->setShowComment($this->dataHelper->getConfig('post_listing/post_comments'));
        $block->setShowView($this->dataHelper->getConfig('post_listing/post_views'));
        $block->setReadTime($this->dataHelper->getConfig('post_listing/post_read_time'));
		$data['list_layout'] = $this->dataHelper->getConfig('author_page/layout');
        $data['grid_col'] = $this->dataHelper->getConfig('author_page/grid_col');
		$block->addData($data);
		return $block->toHtml();
	}
}
