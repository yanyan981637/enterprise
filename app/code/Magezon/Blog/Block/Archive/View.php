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

namespace Magezon\Blog\Block\Archive;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\PostManager;
use Magezon\Blog\Model\ResourceModel\Post\Collection;

class View extends Template
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var PostManager
     */
    protected $postManager;

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param PostManager $postManager
     * @param array $data
     */
	public function __construct(
        Context $context,
        Data $dataHelper,
        PostManager $postManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper  = $dataHelper;
        $this->postManager = $postManager;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $year = $this->getRequest()->getParam('year');
        $month = $this->getRequest()->getParam('month');
        if ($month) {
            $title = $this->dataHelper->getArchiveTitle('month', $year, $month);
        } else {
            $title = $this->dataHelper->getArchiveTitle('year', $year);
        }
        $this->pageConfig->getTitle()->set($title);
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
            $title = $this->getRequest()->getParam('archive_title');
            if ($title) $breadcrumbsBlock->addCrumb('archive', ['label' => $title, 'title' => $title]);
        }
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === NULL) {
            $type  = $this->getRequest()->getParam('archive_type');
            $year  = $this->getRequest()->getParam('year');
            $month = $this->getRequest()->getParam('month');
            switch ($type) {
                case 'year':
                    $collection = $this->postManager->getPostCollectionByYear($year);
                    break;
                
                default:
                    $collection = $this->postManager->getPostCollectionByMonth($year, $month);
                    break;
            }
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
		$block = $this->getLayout()->createBlock(\Magezon\Blog\Block\ListPost::class);
		$block->setCollection($collection);
        $block->setShowPager(true);
        $block->setShowAuthor($this->dataHelper->getConfig('post_listing/post_author'));
        $block->setShowDate($this->dataHelper->getConfig('post_listing/post_date'));
        $block->setShowCategory($this->dataHelper->getConfig('post_listing/post_cats'));
        $block->setShowComment($this->dataHelper->getConfig('post_listing/post_comments'));
        $block->setShowView($this->dataHelper->getConfig('post_listing/post_views'));
        $block->setReadTime($this->dataHelper->getConfig('post_listing/post_read_time'));
		$data['list_layout'] = $this->dataHelper->getConfig('archive_page/layout');
		$data['grid_col'] = $this->dataHelper->getConfig('archive_page/grid_col');
		$block->addData($data);
		return $block->toHtml();
	}
}
