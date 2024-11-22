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

namespace Magezon\Blog\Model\ResourceModel\Comment;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezon\Blog\Model\ResourceModel\Comment;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'comment_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_comment_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'comment_collection';

    /**
     * @var boolean
     */
    protected $_addPostInformation;

    /**
     * @var boolean
     */
    protected $_addCustomerInformation;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init(\Magezon\Blog\Model\Comment::class, Comment::class);
    }

    /**
     * @return $this
     */
    public function addPostInformation()
    {
    	$this->_addPostInformation = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function addCustomerInformation()
    {
        $this->_addCustomerInformation = true;
        return $this;
    }

    /**
     * After collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
    	if ($this->_addPostInformation) {
            $postIds = [];
            foreach ($this as $item) {
                if ($item->getPostId()) {
                    $postIds[] = $item->getPostId();
                }
            }
            if ($postIds) {
				$collection = $this->postCollectionFactory->create();
				$collection->prepareCollection();
				$collection->addCategoryCollection();
				$collection->addAuthorToCollection();
				$collection->addTotalComments();
                $collection->addFieldToFilter('post_id', ['in' => $postIds]);
                foreach ($this as &$item) {
                    if ($item->getPostId() && ($post = $collection->getItemById($item->getPostId()))) {
                        $item->setPost($post);
                    }
                }
            }
        }
        if ($this->_addCustomerInformation) {
            $customerIds = [];
            foreach ($this as $item) {
                if ($item->getCustomerId()) {
                    $customerIds[] = $item->getCustomerId();
                }
            }
            if ($customerIds) {
                $collection = $this->customerCollectionFactory->create();
                $collection->addFieldToFilter('entity_id', ['in' => $customerIds]);
                foreach ($this as &$item) {
                    if ($item->getCustomerId() && ($customer = $collection->getItemById($item->getCustomerId()))) {
                        $item->setCustomer($customer);
                    }
                }
            }
        }
    }
}