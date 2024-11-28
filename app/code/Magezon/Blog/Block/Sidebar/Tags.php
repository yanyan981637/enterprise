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
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Post;
use Magezon\Blog\Model\ResourceModel\Tag\Collection;
use Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magezon\Blog\Model\Tag;

class Tags extends Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var array
     */
    protected $_tags;

    /**
     * @var array
     */
    protected $_postCatgoryList;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param ResourceConnection $resource
     * @param CollectionFactory $collectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        ResourceConnection $resource,
        CollectionFactory $collectionFactory,
        \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext           = $httpContext;
        $this->resource              = $resource;
        $this->collectionFactory     = $collectionFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->dataHelper            = $dataHelper;
    }

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
            'MGZ_BLOG_SIDEBAR_TAGS',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
        ];
        return $cache;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === NULL) {
            $numberOfTags = (int)$this->dataHelper->getConfig('sidebar/tags/number_of_tags');
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('is_active', Tag::STATUS_ENABLED);
            $collection->setPageSize($numberOfTags);
            if ($numberOfTags) {
                $collection->getSelect()->orderRand();
            }
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    public function getPostTagList()
    {
        if ($this->_postCatgoryList === NULL) {
            $ids        = $this->getCollection()->getAllIds();
            $connection = $this->resource->getConnection();
            $table      = $this->resource->getTableName('mgz_blog_tag_post');
            $select     = $connection->select()->from($table)->where('tag_id IN (?)', $ids);
            $result     = $connection->fetchAll($select);

            $postCollection = $this->postCollectionFactory->create();
            $postCollection->getSelect()->joinLeft(
                ['mbcp' => $table],
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
     * @param  Tag $tag
     * @return int
     */
    public function getPostCount($tag)
    {
        $count = 0;
        $list = $this->getPostTagList();
        if ($list) {
            foreach ($list as $_row) {
                if ($_row['tag_id'] == $tag->getId()) $count++;
            }
        }
        return $count;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        if ($this->_tags === NULL) {
            $collection = $this->getCollection();
            $this->_tags = [];
            foreach ($collection as $_tag) {
                $_tag->setPostCount($this->getPostCount($_tag));
                if ($this->getPostCount($_tag) > 0) {
                    $this->_tags[] = $_tag;
                }
            }
        }
        return $this->_tags;
    }
}