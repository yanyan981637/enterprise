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

namespace Magezon\Blog\Block\Post;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Block\ListPost;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Post;

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
        $post = $this->getCurrentPost();
        if ($post) {
            $this->pageConfig->getTitle()->set($post->getMetaTitle() ? $post->getMetaTitle() : $post->getTitle());
            $this->pageConfig->setKeywords($post->getMetaKeywords());
            $this->pageConfig->setDescription($post->getMetaDescription());
            $this->pageConfig->addRemotePageAsset(
                $post->getCanonicalUrl() ? $post->getCanonicalUrl() : $post->getUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
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

            $post = $this->getCurrentPost();
            if ($post && $category = $post->getCategory()) {
                $breadcrumbsBlock->addCrumb(
                    'category',
                    [
                        'label' => $category->getTitle(),
                        'title' => $category->getTitle(),
                        'link'  => $category->getUrl()
                    ]
                );
            }

            if ($post && $title) {
                $breadcrumbsBlock->addCrumb('post',
                    ['label' => $post->getTitle(), 'title' => $post->getTitle()]
                );
            }
        }
    }

    /**
     * Retrieve current post model object
     *
     * @return Post
     */
    public function getCurrentPost()
    {
        return $this->_coreRegistry->registry('current_post');
    }

    /**
     * @return string
     */
	public function getPostListHtml()
	{
		$post = $this->getCurrentPost();
		$collection = $post->getPostCollection();
		$block = $this->getLayout()->createBlock(ListPost::class);
		$block->setCollection($collection);
		$data['list_layout'] = $this->dataHelper->getConfig('post_page/layout');
		$data['grid_col']    = $this->dataHelper->getConfig('post_page/grid_col');
		$block->addData($data);
		return $block->toHtml();
	}

    /**
     * @return string
     */
    public function getVideoId()
    {
        $id = '';
        $post = $this->getCurrentPost();
        $link = $post->getVideoLink();
        if ((strpos($link, 'youtube')!==FALSE || strpos($link, 'vimeo')!==FALSE)) {
            if ((strpos($link, 'youtube')!==FALSE)) {
                preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $link, $matches);
                if ($matches) {
                    $id = $matches[1];
                }
            }
        }
        return $id;
    }

    /**
     * @return string
     */
    public function getVideoLink()
    {
        $post = $this->getCurrentPost();
        $link = $post->getVideoLink();
        if (!$link) return;
        if ((strpos($link, 'youtube')!==FALSE || strpos($link, 'vimeo')!==FALSE)) {
            if (strpos($link, 'youtube')!==FALSE) {
                $link = 'https://www.youtube.com/embed/' . $this->getVideoId();
            }
            if ((strpos($link, 'vimeo')!==FALSE)) {
                $link = str_replace('vimeo.com', 'player.vimeo.com/video', $link);
            }
        } else {
            $link = '';
        }
        return $link;
    }
}
