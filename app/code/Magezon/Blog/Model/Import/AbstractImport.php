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

namespace Magezon\Blog\Model\Import;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magezon\Core\Helper\Data;

abstract class AbstractImport extends DataObject
{
	/**
	 * @var \Magento\Framework\App\ResourceConnection\Connection
	 */
	protected $_connection;

    const IMAGE_DIRECTORY = 'blog';

	/**
	 * @var Collection
	 */
	protected $_postCollection;

	/**
	 * @var \Magezon\Blog\Model\ResourceModel\Category\Collection
	 */
	protected $_categoryCollection;

	/**
	 * @var \Magezon\Blog\Model\ResourceModel\Tag\Collection
	 */
	protected $_tagCollection;

	/**
	 * @var \Magezon\Blog\Model\ResourceModel\Author\Collection
	 */
	protected $_authorCollection;

	/**
	 * @var \Magezon\Blog\Model\ResourceModel\Comment\Collection
	 */
	protected $_commentCollection;

	/**
	 * @var int
	 */
	protected $_nextPostId;

	/**
	 * @var int
	 */
	protected $_nextCategoryId;

	/**
	 * @var int
	 */
	protected $_nextTagId;

	/**
	 * @var int
	 */
	protected $_nextCommentId;

	/**
	 * @var int
	 */
	protected $_nextAuthorId;

	/**
	 * @var array
	 */
	protected $_importedCategories = [];

	/**
	 * @var array
	 */
	protected $_importedTags = [];

	/**
	 * @var array
	 */
	protected $_importedAuthors = [];

	/**
	 * @var array
	 */
	protected $_importedPosts = [];

	/**
	 * @var array
	 */
	protected $_importedComments = [];

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;

    /**
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $coreHelper;

    /**
     * @var CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    protected $tagCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory
     */
    protected $authorCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Comment\CollectionFactory
     */
    protected $commentCollectionFactory;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConnectionFactory $connectionFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param Data $coreHelper
     * @param CollectionFactory $postCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory $authorCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory
     */
	public function __construct(
		GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConnectionFactory $connectionFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        Data $coreHelper,
        CollectionFactory $postCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory $authorCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory,
        array $data = []
	) {
		$this->groupRepository           = $groupRepository;
		$this->searchCriteriaBuilder     = $searchCriteriaBuilder;
		$this->connectionFactory         = $connectionFactory;
		$this->_resourceHelper           = $resourceHelper;
		$this->resource                  = $resource;
		$this->coreHelper                = $coreHelper;
		$this->postCollectionFactory     = $postCollectionFactory;
		$this->categoryCollectionFactory = $categoryCollectionFactory;
		$this->tagCollectionFactory      = $tagCollectionFactory;
		$this->authorCollectionFactory   = $authorCollectionFactory;
		$this->commentCollectionFactory  = $commentCollectionFactory;
        parent::__construct($data);
	}

	abstract protected function _importPosts();

	abstract protected function _importCategories();

	abstract protected function _importTags();

	abstract protected function _importComments();

	abstract protected function _importAuthors();

	/**
	 * @return array
	 */
	public function getImportedCategories()
	{
		return $this->_importedCategories;
	}

	/**
	 * @return array
	 */
	public function getImportedTags()
	{
		return $this->_importedTags;
	}

	/**
	 * @return array
	 */
	public function getImportedAuthors()
	{
		return $this->_importedAuthors;
	}

	/**
	 * @return array
	 */
	public function getImportedPosts()
	{
		return $this->_importedPosts;
	}

	/**
	 * @return array
	 */
	public function getImportedComments()
	{
		return $this->_importedComments;
	}


	protected function getConnection()
	{
		if ($this->_connection === NULL) {
			$host     = $this->getData('host');
			$dbname   = $this->getData('dbname');
			$username = $this->getData('username');
			$password = $this->getData('password');
			try {
				$this->_connection = $this->connectionFactory->create([
					'host'     => $host,
					'dbname'   => $dbname,
					'username' => $username,
					'password' => $password,
					'active'   => '1'
				]);
				$this->_connection->getConnection();
			} catch (\Exception $e) {
				throw new LocalizedException(__('Failed connect to %1', $this->_platform));
			}
	    }
	    return $this->_connection;
	}

	/**
	 * @param  string $name
	 * @return string      
	 */
	public function getTableName($name)
	{
		$tablePrefix = $this->getTablePrefix();
		if ($tablePrefix) $name = $tablePrefix . $name;
		return $name;
	}

	/**
	 * @return string
	 */
	public function getTablePrefix()
    {
    	return $this->getData('table_prefix');
    }

	public function checkConnection()
	{
		$this->getConnection();
	}

	public function import()
	{
		$this->checkConnection();
		$this->_importCategories();
		$this->_importTags();
		$this->_importAuthors();
		$this->_importPosts();
		$this->_importComments();
	}

	/**
	 * @return string
	 */
	public function getPostTable()
	{
    	return $this->resource->getTableName('mgz_blog_post');
	}

	/**
	 * @return string
	 */
	public function getPostStoreTable()
	{
    	return $this->resource->getTableName('mgz_blog_post_store');
	}

	/**
	 * @return string
	 */
	public function getPostCustomerGroupTable()
	{
    	return $this->resource->getTableName('mgz_blog_post_customer_group');
	}

	/**
	 * @return Collection
	 */
	protected function getPostCollection()
	{
		if ($this->_postCollection == NULL) {
	        $this->_postCollection = $this->postCollectionFactory->create();
		}
        return $this->_postCollection;
	}

    /**
     * @param $identifier
     * @return DataObject|null
     */
	public function getPost($identifier)
	{
		$collection = $this->getPostCollection();
		return $this->getPostCollection()->getItemByColumnValue('identifier', $identifier);
	}

	/**
     * Retrieve next post id
     *
     * @return int
     */
    protected function _getNextPostId()
    {
        if (!$this->_nextPostId) {
            $this->_nextPostId = $this->_resourceHelper->getNextAutoincrement($this->getPostTable());
        }
        return $this->_nextPostId++;
    }

    /**
     * @return string
     */
	public function getCategoryTable()
	{
    	return $this->resource->getTableName('mgz_blog_category');
	}

	/**
	 * @return string
	 */
	public function getCategoryStoreTable()
	{
    	return $this->resource->getTableName('mgz_blog_category_store');
	}

	/**
	 * @return string
	 */
	public function getCategoryPostTable()
	{
    	return $this->resource->getTableName('mgz_blog_category_post');
	}

    /**
     * @param $identifier
     * @return DataObject|null
     */
	public function getCategory($identifier)
	{
		return $this->getCategoryCollection()->getItemByColumnValue('identifier', $identifier);
	}

	/**
	 * @return \Magezon\Blog\Model\ResourceModel\Category\Collection
	 */
	protected function getCategoryCollection()
	{
		if ($this->_categoryCollection == NULL) {
	        $this->_categoryCollection = $this->categoryCollectionFactory->create();
		}
        return $this->_categoryCollection;
	}

	/**
     * Retrieve next category id
     *
     * @return int
     */
    protected function _getNextCategoryId()
    {
        if (!$this->_nextCategoryId) {
            $this->_nextCategoryId = $this->_resourceHelper->getNextAutoincrement($this->getCategoryTable());
        }
        return $this->_nextCategoryId++;
    }

    /**
     * @return string
     */
	public function getAuthorTable()
	{
    	return $this->resource->getTableName('mgz_blog_author');
	}

    /**
     * @return \Magezon\Blog\Model\ResourceModel\Author\Collection
     */
	protected function getAuthorCollection()
	{
		if ($this->_authorCollection == NULL) {
	        $this->_authorCollection = $this->authorCollectionFactory->create();
		}
        return $this->_authorCollection;
	}

    /**
     * @param $identifier
     * @return DataObject|null
     */
	public function getAuthor($identifier)
	{
		return $this->getAuthorCollection()->getItemByColumnValue('identifier', $identifier);
	}

	/**
     * Retrieve next author id
     *
     * @return int
     */
    protected function _getNextAuthorId()
    {
        if (!$this->_nextAuthorId) {
            $this->_nextAuthorId = $this->_resourceHelper->getNextAutoincrement($this->getAuthorTable());
        }
        return $this->_nextAuthorId++;
    }

    /**
     * @return string
     */
	public function getTagTable()
	{
    	return $this->resource->getTableName('mgz_blog_tag');
	}

    /**
     * @return string
     */
	public function getTagPostTable()
	{
    	return $this->resource->getTableName('mgz_blog_tag_post');
	}

	/**
	 * @return \Magezon\Blog\Model\ResourceModel\Tag\Collection
	 */
	protected function getTagCollection()
	{
		if ($this->_tagCollection == NULL) {
	        $this->_tagCollection = $this->tagCollectionFactory->create();
		}
        return $this->_tagCollection;
	}

    /**
     * @param $identifier
     * @return DataObject|null
     */
	public function getTag($identifier)
	{
		return $this->getTagCollection()->getItemByColumnValue('identifier', $identifier);
	}

	/**
     * Retrieve next tag id
     *
     * @return int
     */
    protected function _getNextTagId()
    {
        if (!$this->_nextTagId) {
            $this->_nextTagId = $this->_resourceHelper->getNextAutoincrement($this->getTagTable());
        }
        return $this->_nextTagId++;
    }

    /**
     * @return string
     */
	public function getCommentTable()
	{
    	return $this->resource->getTableName('mgz_blog_comment');
	}

	/**
	 * @return \Magezon\Blog\Model\ResourceModel\Comment\Collection
	 */
	protected function getCommentCollection()
	{
		if ($this->_commentCollection == NULL) {
	        $this->_commentCollection = $this->commentCollectionFactory->create();
		}
        return $this->_commentCollection;
	}

	/**
     * Retrieve next comment id
     *
     * @return int
     */
    protected function _getNextCommentId()
    {
        if (!$this->_nextCommentId) {
            $this->_nextCommentId = $this->_resourceHelper->getNextAutoincrement($this->getCommentTable());
        }
        return $this->_nextCommentId++;
    }

    /**
     * @return array
     */
    public function getCustomGroups()
    {
    	return $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBehavior()
    {
    	if (!$this->getData('behavior')) return Import::BEHAVIOR_APPEND;
    	return $this->getData('behavior');
    }

    /**
     * @param $image
     * @return string|void
     */
    public function getImagePath($image)
    {
    	if ($image) return self::IMAGE_DIRECTORY . '/' . $image;
    	return;
    }
}