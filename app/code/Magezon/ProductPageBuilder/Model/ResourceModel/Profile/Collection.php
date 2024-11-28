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
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Model\ResourceModel\Profile;

use Magento\Store\Model\Store;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'profile_id';

    protected function _construct()
    {
        $this->_init(
            \Magezon\ProductPageBuilder\Model\Profile::class,
            \Magezon\ProductPageBuilder\Model\ResourceModel\Profile::class
        );
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('mgz_productpagebuilder_profile_store', 'profile_id');
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
        if ($this->getFilter('store_table.store_id')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = store_table.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        
        $this->performAfterLoad('mgz_productpagebuilder_profile_store', 'profile_id', 'store_id');
        return parent::_afterLoad();
    }

    /**
     * @return Collection|void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('profile_id', 'main_table.profile_id');
        return parent::_initSelect();
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
        $id = $this->getColumnValues($linkField);
        if (count($id)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['customer_data' => $this->getTable($tableName)])
                ->where('customer_data.' . $linkField . ' IN (?)', $id);
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
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return $this|void
     */
    public function addStoreFilter($store, $withAdmin = true)
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
        $this->addFilter('store_table.store_id', ['in' => $store], 'public');

        return $this;
    }
}
