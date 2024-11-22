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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Model\ResourceModel\File;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\ProductAttachments\Model\File;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'file_id';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            File::class,
            \Magezon\ProductAttachments\Model\ResourceModel\File::class
        );
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinTable('store', 'mgz_product_attachments_store', 'file_id');
        $this->joinTable('customergroup_table', 'mgz_product_attachments_customer_group', 'file_id');
        parent::_renderFiltersBefore();
    }

    /**
     * @param $alias
     * @param $tableName
     * @param $linkField
     */
    protected function joinTable($alias, $tableName, $linkField)
    {
        $this->getSelect()->joinLeft(
            [$alias => $this->getTable($tableName)],
            'main_table.' . $linkField . ' = ' . $alias . '.' . $linkField,
            []
        )->group(
            'main_table.' . $linkField
        );
        return $this;
    }


    /**
     * Load total downloads
     *
     * @return $this
     */
    public function addTotalDownloads()
    {
        $this->getSelect()->joinLeft(
            ['mpar' => $this->getTable('mgz_product_attachments_report')],
            'main_table.file_id = mpar.file_id',
            [
                'total_downloads' => 'COUNT(DISTINCT mpar.report_id)'
            ]
        )->group('main_table.file_id');
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('mgz_product_attachments_store', 'file_id', 'store_id');
        $this->performAfterLoad('mgz_product_attachments_customer_group', 'file_id', 'customer_group_id');
        return parent::_afterLoad();
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('file_id', 'main_table.file_id');
        return parent::_initSelect();
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @param $field
     * @return void
     */
    protected function performAfterLoad($tableName, $linkField, $field)
    {
        $values = $this->getColumnValues($linkField);
        if (count($values)) {
            $connection = $this->getConnection();

            $select = $connection->select()
                ->from(['customer_data' => $this->getTable($tableName)])
                ->where('customer_data.' . $linkField . ' IN (?)', $values);
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
                    $item->setData($field, $storesData[$linkedId]);
                }
            }
        }
    }

    /**
     * Add filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);
        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
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

        $this->addFilter('store.store_id', ['in' => $store], 'public');
    }

    /**
     * Filter file by customer group id
     *
     * @param $customerGroupId
     * @param bool $withAdmin
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroupId, $withAdmin = true)
    {
        $this->performAddCustomerGroupAfterLoad($customerGroupId, $withAdmin);
        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param $customerGroup
     * @return void
     */
    protected function performAddCustomerGroupAfterLoad($customerGroup)
    {
        if (!is_array($customerGroup)) {
            $customerGroup = [$customerGroup];
        }

        $this->addFilter('customer_group_id', ['in' => $customerGroup], 'public');
    }

    /**
     * Filter files by customer group, store and status
     *
     * @return $this
     */
    public function prepareCollection()
    {
        $groupId = $this->customerSession->getCustomerGroupId();
        $store = $this->storeManager->getStore();
        $this->addCustomerGroupFilter($groupId);
        $this->addIsActiveFilter();
        $this->addStoreFilter($store);
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
            $this->addFieldToFilter('main_table.is_active', (int)$isActive ? 1 : 0);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }
}
