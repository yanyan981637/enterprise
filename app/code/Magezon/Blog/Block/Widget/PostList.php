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

namespace Magezon\Blog\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magezon\Blog\Block\ListPost;
use Magezon\Blog\Model\Post;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magezon\Core\Helper\Data;

class PostList extends Template implements BlockInterface
{
    /**
     * Slider layout widget
     */
    const LAYOUT_SLIDER = 'slider';

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Data
     */
    protected $coreHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magezon\Blog\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Data $coreHelper
     * @param CollectionFactory $collectionFactory
     * @param \Magezon\Blog\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        Data $coreHelper,
        CollectionFactory $collectionFactory,
        \Magezon\Blog\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->coreHelper = $coreHelper;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->dataHelper->isEnabled()) {
            return;
        }
        return parent::_toHtml();
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->getData('template')) {
            $this->setTemplate($this->getData('template'));
        } else {
            $this->setTemplate('widget/post_list.phtml');
        }
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [Post::CACHE_TAG,
            ]]);
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheInfo = [
            'BLOG_POSTLIST_WIDGET',
            $this->_storeManager->getStore()->getId(),
            (int) $this->_storeManager->getStore()->isCurrentlySecure(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            $this->coreHelper->serialize($this->getData()),
        ];

        return $cacheInfo;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection == null) {
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection();
            $collection->addAuthorToCollection();
            $collection->addTotalComments();
            if ($this->getData('featurepost')) {
                $collection->addFieldToFilter('featured', 1);
            }
            if ($limit = $this->getData('limit')) {
                $collection->setPageSize((int) $limit);
            }
            $categories = $this->getData('categories');
            if ($categories) {
                $categories = explode(",", $categories);
                $collection->getSelect()->joinLeft(
                    ['mbcp' => $collection->getResource()->getTable('mgz_blog_category_post')],
                    'main_table.post_id = mbcp.post_id',
                    []
                )->group('main_table.post_id');
                $collection->getSelect()->where('category_id IN (?)', $categories);
            }
            $postIds = $this->getData('post_ids');
            if ($postIds) {
                $postIds = explode(",", $postIds);
                $collection->getSelect()->orWhere('main_table.post_id IN (?)', $postIds);
            }
            $collection->setOrder('publish_date', 'DESC');
            $collection->setOrder('creation_time', 'DESC');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    public function getOwlCarouselOptions()
    {
        $options['responsive'][1200]['items'] = $this->getData('owl_item_xl') ? $this->getData('owl_item_xl') : 4;
        $options['responsive'][992]['items'] = $this->getData('owl_item_lg') ? $this->getData('owl_item_lg') : 4;
        $options['responsive'][768]['items'] = $this->getData('owl_item_md') ? $this->getData('owl_item_md') : 3;
        $options['responsive'][544]['items'] = $this->getData('owl_item_sm') ? $this->getData('owl_item_sm') : 2;
        $options['responsive'][0]['items'] = $this->getData('owl_item_xs') ? $this->getData('owl_item_xs') : 1;
        $lazyLoad = $this->getData('owl_lazyload');
        $options['nav'] = $this->getData('owl_nav') ? true : false;
        $options['dots'] = $this->getData('owl_dots') ? true : true;
        $options['autoplayHoverPause'] = $this->getData('owl_autoplay_hover_pause') ? true : false;
        $options['autoplay'] = $this->getData('owl_autoplay') ? true : false;
        $options['autoplayTimeout'] = $this->getData('owl_autoplay_timeout');
        $options['lazyLoad'] = $lazyLoad ? true : true;
        $options['loop'] = $this->getData('owl_loop') ? true : false;
        $options['margin'] = (int) $this->getData('owl_margin') ? (int) $this->getData('owl_margin') : 15;
        $options['autoHeight'] = $this->getData('owl_auto_height') ? true : false;
        $options['rtl'] = $this->getData('owl_rtl') ? true : false;
        $options['center'] = $this->getData('owl_center') ? true : false;
        $options['slideBy'] = $this->getData('owl_slide_by') ? $this->getData('owl_slide_by') : 1;
        $options['animateIn'] = $this->getData('owl_animate_in') ? $this->getData('owl_animate_in') : '';
        $options['animateOut'] = $this->getData('owl_animate_out') ? $this->getData('owl_animate_out') : '';
        $options['stagePadding'] = $this->getData('owl_stage_padding') ? (int) $this->getData('owl_stage_padding') : 0;
        if ($this->getData('owl_dots_speed')) {
            $options['dotsSpeed'] = $this->getData('owl_dots_speed');
        }
        if ($this->getData('owl_autoplay_speed')) {
            $options['autoplaySpeed'] = $this->getData('owl_autoplay_speed');
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getPostListHtml()
    {
        $collection = $this->getCollection();
        $block = $this->getLayout()->createBlock(ListPost::class);
        if($this->getData('list_layout') == self::LAYOUT_SLIDER) {
            $block->setTemplate('Magezon_Blog::post/slider.phtml');
        }
        $block->setCollection($collection);
        $block->setShowAuthor($this->getData('post_author'));
        $block->setShowCategory($this->getData('post_cats'));
        $block->setShowComment($this->getData('post_comments'));
        $block->setShowView($this->getData('post_views'));
        $block->setReadTime($this->getData('read_time'));
        $block->setShowDate($this->getData('post_date'));
        $block->setShowExcerpt($this->getData('post_excerpt'));
        $block->setOwlCarouselOptions($this->getOwlCarouselOptions());
        $data['list_layout'] = $this->getData('list_layout');
        $data['grid_col'] = $this->getData('grid_col');
        $block->addData($data);
        return $block->toHtml();
    }
}
