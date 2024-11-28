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

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Magezon\Blog\Model\Post;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Core\Helper\Data;

class ListPost extends Template
{
    /**
     * Default value for posts count that will be shown
     */
    const DEFAULT_POSTS_COUNT            = 10;
    const DEFAULT_POSTS_PER_PAGE         = 10;
    const DEFAULT_SHOW_PAGER             = false;
    const DEFAULT_SHOW_NO_RESULT_MESSAGE = true;
    const GRID_COL                       = 2;

    /**
     * Name of request parameter for page number value
     *
     * @deprecated
     */
    const PAGE_VAR_NAME = 'np';

    /**
     * @var string
     */
    protected $_template = 'Magezon_Blog::post/list.phtml';

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @var Pager
     */
    protected $pager;

    /**
     * @var integer
     */
    protected $_pageSize;

    /**
     * @var Data
     */
    protected $coreHelper;


    /**
     * @param Context $context
     * @param Data $coreHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreHelper = $coreHelper;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        $collection = $this->_collection;
        if ($this->showPager()) {
            $collection->setPageSize(
                $this->getPageSize())->setCurPage($this->getRequest()->getParam($this->getPageVarName(), 1)
            );
        }
        return $this->_collection->load();
    }

    /**
     * @param Collection $collection
     */
    public function setCollection(Collection $collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * @return string
     */
    public function getListLayout()
    {
        if (!$this->hasData('list_layout')) {
            $this->setData('list_layout', Post::LAYOUT_FIXED_THUMBNAIL);
        }
        return $this->getData('list_layout');
    }

    /**
     * @return array|int|mixed|null
     */
    public function getListColumn()
    {
        if (!$this->hasData('grid_col')) {
            $this->setData('grid_col', self::GRID_COL);
        }
        return $this->getData('grid_col') ? $this->getData('grid_col') : self::GRID_COL;
    }

    /**
     * Retrieve how many posts should be displayed
     *
     * @return int
     */
    public function getPostsCount()
    {
        if ($this->hasData('posts_count')) {
            return $this->getData('posts_count');
        }

        if (null === $this->getData('posts_count')) {
            $this->setData('posts_count', self::DEFAULT_POSTS_COUNT);
        }

        return $this->getData('posts_count');
    }

    /**
     * Retrieve how many posts should be displayed
     *
     * @return int
     */
    public function getPostsPerPage()
    {
        if (!$this->hasData('posts_per_page')) {
            $this->setData('posts_per_page', self::DEFAULT_POSTS_PER_PAGE);
        }
        return $this->getData('posts_per_page');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showNoResultText()
    {
        if (!$this->hasData('show_no_result_text')) {
            $this->setData('show_no_result_text', self::DEFAULT_SHOW_NO_RESULT_MESSAGE);
        }
        return (bool)$this->getData('show_no_result_text');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function getPageVarName()
    {
        if (!$this->hasData('page_var_name')) {
            $this->setData('page_var_name', self::PAGE_VAR_NAME);
        }
        return $this->getData('page_var_name');
    }

    /**
     * @return bool
     */
    public function getShowAuthor()
    {
        if (!$this->hasData('show_author')) {
            $this->setData('show_author', true);
        }
        return (bool)$this->getData('show_author');
    }

    /**
     * @return bool
     */
    public function getShowDate()
    {
        if (!$this->hasData('show_date')) {
            $this->setData('show_date', true);
        }
        return (bool)$this->getData('show_date');
    }

    /**
     * @return bool
     */
    public function getShowImage()
    {
        if (!$this->hasData('show_image')) {
            $this->setData('show_image', true);
        }
        return (bool)$this->getData('show_image');
    }

    /**
     * @return bool
     */
    public function getShowCategory()
    {
        if (!$this->hasData('show_category')) {
            $this->setData('show_category', true);
        }
        return (bool)$this->getData('show_category');
    }

    /**
     * @return bool
     */
    public function getShowComment()
    {
        if (!$this->hasData('show_comment')) {
            $this->setData('show_comment', true);
        }
        return (bool)$this->getData('show_comment');
    }

    /**
     * @return bool
     */
    public function getShowView()
    {
        if (!$this->hasData('show_view')) {
            $this->setData('show_view', true);
        }
        return (bool)$this->getData('show_view');
    }

    /**
     * @return bool
     */
    public function getShowReadTime()
    {
        if (!$this->hasData('read_time')) {
            $this->setData('read_time', true);
        }
        return (bool)$this->getData('read_time');
    }

    /**
     * @return bool
     */
    public function getShowExcerpt()
    {
        if (!$this->hasData('show_excerpt')) {
            $this->setData('show_excerpt', true);
        }
        return (bool)$this->getData('show_excerpt');
    }

    /**
     * Retrieve how many posts should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        if ($this->_pageSize !== NULL) return $this->_pageSize;
        return $this->showPager() ? $this->getPostsPerPage() : $this->getPostsCount();
    }

    /**
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->_pageSize = $pageSize;
        return $this;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $size = $this->getCollection()->getSize();
        if ($this->showPager() && $size > $this->getPostsPerPage() && $this->getPostsPerPage()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    Pager::class
                );
                $this->pager->setNameInLayout('blog_pager');
                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getPageVarName())
                    ->setLimit($this->getPostsPerPage())
                    ->setTotalLimit($this->getPostsCount())
                    ->setCollection($this->getCollection());
            }
            if ($this->pager instanceof AbstractBlock) {
                return $this->pager->toHtml();
            }
        }
        return '';
    }
}
