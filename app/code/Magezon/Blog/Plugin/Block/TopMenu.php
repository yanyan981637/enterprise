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

namespace Magezon\Blog\Plugin\Block;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Category;
use Magezon\Blog\Model\ResourceModel\Category\Collection;
use Magezon\Blog\Model\ResourceModel\Category\CollectionFactory;

class TopMenu
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
     * @var NodeFactory
     */
    protected $nodeFactory;

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

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    protected $_items;

    protected $_menu;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param NodeFactory $nodeFactory
     * @param ResourceConnection $resource
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        NodeFactory $nodeFactory,
        ResourceConnection $resource,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->nodeFactory           = $nodeFactory;
        $this->resource              = $resource;
        $this->dataHelper            = $dataHelper;
        $this->collectionFactory     = $collectionFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->_storeManager         = $storeManager;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            $storeId =  $this->_storeManager->getStore()->getId();
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection($storeId);
            $collection->addFieldToFilter('include_in_menu', 1);
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
            $ids = $this->getCollection()->getAllIds();
            $connection = $this->resource->getConnection();
            $select = $connection->select()->from($this->resource->getTableName('mgz_blog_category_post'))
                ->where('category_id IN (?)', $ids);
            $result = $connection->fetchAll($select);

            $postCollection = $this->postCollectionFactory->create();
            $postCollection->getSelect()->joinLeft(
                ['mbcp' => $this->resource->getTableName('mgz_blog_category_post')],
                'main_table.post_id = mbcp.post_id',
                []
            )->group('main_table.post_id');
            $postCollection->prepareCollection();

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
     * @return int
     */
    public function getPostCount(Category $category)
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
                $children = $this->prepareList($_category);
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
     * @return array
     */
    private function prepareList(Category $category)
    {
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
                if ($hasChildren && ($_children = $this->prepareList($children))) {
                    $children->setChildren($_children);
                }
                $childrens[] = $children;
            }
        }
        return $childrens;
    }

    /**
     * @param $node
     * @return string
     */
    public function prepareMenu($node)
    {
        $html = '';
        $categories = $this->getCategories();
        foreach ($categories as $category) {
//            if ($category->getPostCount() > 0) {
            $html .= $this->prepareItem($category, $node);
//            }
        }
        return $html;
    }

    /**
     * @param $category
     * @param $node
     * @param $level
     * @return void
     */
    public function prepareItem($category, $node, $level = 1)
    {
        $maxDepth = (int)$this->getMaxDepth();
        if ($maxDepth && ($level > $maxDepth)) {
            return;
        }
        $title = $category->getTitle();
        if ($this->showPostCount()) {
            $title .= ' (' . $category->getPostCount() . ')';
        }
        $item = $this->nodeFactory->create(
            [
                'data'    => [
                    'name' => $title,
                    'id'   => 'blog-note' . $category->getId(),
                    'url'  => $category->getUrl()
                ],
                'idField' => 'id',
                'tree'    => $this->_menu->getTree()
            ]
        );
        $children = $category->getChildren();
        if ($children) {
            foreach ($children as $_category) {
                $this->prepareItem($_category, $item, ++$level);
            }
        }
        $node->addChild($item);
    }

    /**
     * @return boolean
     */
    public function showPostCount()
    {
        return $this->dataHelper->getConfig('top_navigation/show_post_count');
    }

    /**
     * @return int
     */
    public function getMaxDepth()
    {
        return $this->dataHelper->getConfig('top_navigation/max_depth');
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param $outermostClass
     * @param $childrenWrapClass
     * @param $limit
     * @return void
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        if ($this->dataHelper->getConfig('top_navigation/enabled') && $this->dataHelper->isEnabled()) {
            $this->_menu = $subject->getMenu();
            $hasActive = false;
            $title = $this->dataHelper->getConfig('top_navigation/title');
            $blog  = $this->nodeFactory->create(
                [
                    'data'    => [
                        'name' => $title,
                        'id'   => 'blog-note',
                        'url'  => $this->dataHelper->getBLogUrl(),
                    ],
                    'idField' => 'id',
                    'tree'    => $subject->getMenu()->getTree()
                ]
            );
            if ($this->dataHelper->getConfig('top_navigation/include_categories')) {
                $this->prepareMenu($blog);
            }
            $subject->getMenu()->addChild($blog);
        }
    }
}
