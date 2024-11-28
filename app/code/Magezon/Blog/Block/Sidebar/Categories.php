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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Category;
use Magezon\Blog\Model\Post;
use Magezon\Blog\Model\ResourceModel\Category\Collection;
use Magezon\Blog\Model\ResourceModel\Category\CollectionFactory;

class Categories extends Template
{
    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @var array
     */
    protected $_categories;

    /**
     * @var array
     */
    protected $_postCatgoryList;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;

    protected $_items;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param ResourceConnection $resource
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        ResourceConnection $resource,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext           = $httpContext;
        $this->resource              = $resource;
        $this->dataHelper            = $dataHelper;
        $this->collectionFactory     = $collectionFactory;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * @return void
     */
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
            'MGZ_BLOG_SIDEBAR_CATEGORIES',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
        ];
        return $cache;
    }

    /**
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            $storeId =  $this->_storeManager->getStore()->getId();
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection($storeId);
            $collection->setOrder('position', 'ASC');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    public function getPostCategoryList()
    {
        if ($this->_postCatgoryList === null) {
            $table          = $this->resource->getTableName('mgz_blog_category_post');
            $ids            = $this->getCollection()->getAllIds();
            $connection     = $this->resource->getConnection();
            $select         = $connection->select()->from($table)->where('category_id IN (?)', $ids);
            $result         = $connection->fetchAll($select);
            $postCollection = $this->postCollectionFactory->create();
            $postCollection->prepareCollection();
            $postCollection->getSelect()->joinLeft(
                ['mbcp' => $table],
                'main_table.post_id = mbcp.post_id',
                []
            )->group('main_table.post_id');

            foreach ($result as $k => $row) {
                if (!$postCollection->getItemById($row['post_id'])) {
                    unset($result[$k]);
                }
            }
            $this->_postCatgoryList = array_values($result);
        }
        return $this->_postCatgoryList;
    }

    /**
     * @param  Category $category
     * @return integer
     */
    public function getPostCount($category)
    {
        $count = 0;
        $list = $this->getPostCategoryList();
        if ($list) {
            foreach ($list as $_row) {
                if ($_row['category_id'] == $category->getId()) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        if ($this->_categories === null) {
            $showProductCount = $this->showPostCount();
            $categories = [];
            $ids = $this->getCollection()->getAllIds();

            $items = $this->getCollection()->getItems();
            foreach ($items as $k => $_category) {
                if (!$_category->getParentId()) {
                    $categories[] = $_category;
                    unset($items[$k]);
                }
                if ($showProductCount) {
                    $_category->setPostCount($this->getPostCount($_category));
                }
            }
            $this->_items = $items;
            foreach ($categories as &$_category) {
                $children = $this->prepareList($_category, 2);
                if ($children) {
                    $_category->setChildren($children);
                }
            }
            $this->_categories = $categories;
        }

        return $this->_categories;
    }

    /**
     * @param  Category $category
     * @param  integer $level
     * @return array
     */
    private function prepareList($category, $level)
    {
        $maxDepth = (int)$this->getMaxDepth();
        if ($maxDepth && ($level > $maxDepth)) {
            return;
        }
        $childrens = [];
        foreach ($this->_items as $k => $_category) {
            if ($_category->getParentId() == $category->getId()) {
                $hasChildren = false;
                $children = $_category;
                foreach ($this->_items as $_category2) {
                    if ($_category2->getParentId() == $_category->getId()) {
                        $hasChildren = true;
                        break;
                    }
                }
                if ($hasChildren && ($_children = $this->prepareList($children, ++$level))) {
                    $children->setChildren($_children);
                }
                $childrens[] = $children;
            }
        }
        return $childrens;
    }

    /**
     * @return string
     */
    public function getCategoriesHtml()
    {
        $html = '';
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $html .= $this->getCommentHtml($category);
        }
        return $html;
    }

    /**
     * @param  Category $category
     * @return string
     */
    public function getCommentHtml($category)
    {
        $children = $category->getChildren();
        $html = '';
            $html .= '<li id="blog-category-' . $category->getId() . '" ' . ($children ? 'class="blog-category-parent"' : '') . '>';
            $html .= '<a href="' . $category->getUrl() . '">' . $category->getTitle();
            if ($this->showPostCount()) {
                $html .= ' (' . $category->getPostCount() . ')';
            }
            if ($children) {
                $html .= '<i class="blog-category-caret fas mgz-fa-plus"></i>';
            }
            $html .= '</a>';
            if ($children) {
                $html .= '<ul class="blog-category-children">';
                foreach ($children as $_category) {
                    $html .= $this->getCommentHtml($_category);
                }
                $html .= '</ul>';
            }
            $html .= '</li>';
        return $html;
    }

    /**
     * @return int
     */
    public function getMaxDepth()
    {
        return (int)$this->dataHelper->getConfig('sidebar/categories/max_depth');
    }

    /**
     * @return string|null
     */
    public function showPostCount()
    {
        return $this->dataHelper->getConfig('sidebar/categories/show_post_count');
    }
}
