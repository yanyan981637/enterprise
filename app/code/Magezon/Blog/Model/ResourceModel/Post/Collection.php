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

namespace Magezon\Blog\Model\ResourceModel\Post;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Model\Category;
use Magezon\Blog\Model\Comment;
use Magezon\Blog\Model\ResourceModel\Post;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'post_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_post_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'post_collection';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory
     */
    protected $authorCollectionFactory;

    /**
     * @var boolean
     */
    protected $_addCategoryCollection;

    /**
     * @var boolean
     */
    protected $_addAuthor;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory $authorCollectionFactory
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory $authorCollectionFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->authorCollectionFactory = $authorCollectionFactory;
    }

    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magezon\Blog\Model\Post::class, Post::class);
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['post_id'] = 'main_table.post_id';
        $this->_map['fields']['customergroup'] = 'customergroup_table.customer_group_id';
    }

    /**
     * Before collection load
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_before', [$this->_eventObject => $this]);
        return parent::_beforeLoad();
    }

    /**
     * After collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', [$this->_eventObject => $this]);
        $this->performAfterLoad('mgz_blog_post_store', 'post_id', 'store_id');
        $this->performCustomerGroupAfterLoad('mgz_blog_post_customer_group', 'post_id', 'customer_group_id');

        if ($this->_addCategoryCollection) {
            $ids = $this->getAllIds();
            $table = $this->getResource()->getTable('mgz_blog_category_post');
            $connection = $this->getResource()->getConnection();
            $select = $connection->select()->from($table)->where('post_id IN (?)', $ids);
            $list = $connection->fetchAll($select);
            $collection = $this->_categoryCollectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['mbcp' => $this->getResource()->getTable('mgz_blog_category_post')],
                'main_table.category_id = mbcp.category_id',
                []
            )->where(
                'mbcp.post_id IN (?)', $ids
            )->group('main_table.category_id');
            $collection->addFieldToFilter('is_active', Category::STATUS_ENABLED);

            foreach ($this as &$item) {
                $_categories = [];
                foreach ($list as $_row) {
                    if (($_row['post_id'] == $item->getId()) && ($_category = $collection->getItemById($_row['category_id']))) {
                        $_categories[] = $_category;
                    }
                }
                $item->setCategoryList($_categories);
            }
        }

        if ($this->_addAuthor) {
            $authorIds = [];
            foreach ($this as $item) {
                if ($item->getAuthorId()) {
                    $authorIds[] = $item->getAuthorId();
                }
            }
            if ($authorIds) {
                $authorCollection = $this->authorCollectionFactory->create();
                $authorCollection->addFieldToFilter('author_id', ['in' => $authorIds]);
                foreach ($this as &$item) {
                    if ($item->getAuthorId()
                        && ($author = $authorCollection->getItemById($item->getAuthorId())) && $author->isActive()
                    ) {
                        $item->setAuthor($author);
                    }
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * @param $store
     * @param $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);
        return $this;
    }

    /**
     * @param $store
     * @param $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    public function addCustomerGroupFilter($customerGroupId, $withAdmin = true)
    {
        $this->performAddCustomerGroupAfterLoad($customerGroupId, $withAdmin);
        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddCustomerGroupAfterLoad($customerGroup)
    {
        if (!is_array($customerGroup)) {
            $customerGroup = [$customerGroup];
        }

        $this->addFilter('customergroup', ['in' => $customerGroup], 'public');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('mgz_blog_post_store', 'post_id');
        $this->joinCustomerGroupRelationTable('customergroup_table', 'mgz_blog_post_customer_group', 'post_id');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinCustomerGroupRelationTable($alias, $tableName, $linkField)
    {
        if ($this->getFilter('customergroup')) {
            $this->getSelect()->join(
                [$alias => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = ' . $alias . '.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $linkField)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = store_table.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function performAfterLoad($tableName, $linkField, $field)
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['blog_entity_store' => $this->getTable($tableName)])
                ->where('blog_entity_store.' . $linkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData[$field];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                    if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData($field, $storesData[$linkedId]);
                }
            }
        }
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function performCustomerGroupAfterLoad($tableName, $linkField, $field)
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getTable($tableName))->where(
                $linkField . ' IN (?)',
                $linkedIds
            );
            $result = $connection->fetchAll($select);
            if ($result) {
                $data = [];
                foreach ($result as $item) {
                    $data[$item[$linkField]][] = $item[$field];
                }
                foreach ($this as &$item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($data[$linkedId])) {
                        continue;
                    }
                    $item->setData($field, $data[$linkedId]);
                }
            }
        }
    }

    public function addCategoryCollection()
    {
        $this->_addCategoryCollection = true;
        return $this;
    }

    public function addAuthorToCollection()
    {
        $this->_addAuthor = true;
        return $this;
    }

    public function addTotalComments()
    {
        $this->getSelect()->joinLeft(
            ['mbc' => $this->getTable('mgz_blog_comment')],
            'main_table.post_id = mbc.post_id AND mbc.status = ' . Comment::STATUS_APPROVED,
            ['total_comments' => 'COUNT(mbc.post_id)']
        )->group('main_table.post_id');
        return $this;
    }

    public function prepareCollection($storeId = Store::DEFAULT_STORE_ID)
    {
        $store = $this->storeManager->getStore();
        $groupId = $this->customerSession->getCustomerGroupId();
        $this->addIsActiveFilter()
            ->addStoreFilter($store)
            ->addCustomerGroupFilter($groupId)
            ->addCategoryCollection();
            $this->addFieldToFilter('end_date', [
                ['gt' => date("Y-m-d H:i:s")],
                ['null' => true],
            ])
            ->addFieldToFilter('publish_date', [
                ['lteq' => date("Y-m-d H:i:s")]
            ]);
        return $this;
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('main_table.is_active', (int) $isActive ? 1 : 0);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }
}
