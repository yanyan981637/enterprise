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
 * @package   Magezon_ProductLabel
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductLabels\Model;

class Processor extends \Magezon\Core\Model\ConditionsProcessor
{   
    /**
     * @param  \Magezon\ProductLabels\Model\Label $labels 
     * @return array                                    
     */
    public function process(\Magezon\ProductLabels\Model\Label $labels)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            $store = $this->_storeManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $this->__processBystore($labels, $store);
        } else {
            $storeIds = (array)$labels->getData('store_id');
            if (!empty($storeIds)) {
                if (in_array(0, $storeIds)) {
                    $stores = $this->_systemStore->getStoreValuesForForm();
                    foreach ($stores as $store) {
                        if (is_array($store['value']) && !empty($store['value'])) {
                            foreach ($store['value'] as $_store) {
                                $store = $this->_storeManager->getStore($_store['value']);
                                $this->__processBystore($labels, $store);
                            }
                        }
                    }
                } else {
                    foreach ($storeIds as $storeId) {
                        $store = $this->_storeManager->getStore($storeId);
                        $this->__processBystore($labels, $store);
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function __processBystore($labels, $store)
    {
        $collection = $this->getProductByConditions($labels, $store);
        $this->_saveTabRuleProduct($labels, $collection, $store);
    }

    protected function _saveTabRuleProduct($labels, $collection, $store)
    {
        $labelsId = $labels->getId();
        $table = $this->_resource->getTableName('mgz_productlabels_label_product');
        $connection = $this->_resource->getConnection();
        $newRecords = [];
        $items = $collection->getItems();
        foreach ($items as $_item) {
            $newRecords[] = $_item->getId();
        }
        $where = ['label_id = ?' => $labelsId, 'store_id = ?' => $store->getId()];
        $connection->delete($table, $where);
        if ($newRecords) {
            $data = [];
            foreach ($newRecords as $productId) {
                $data[] = [
                    'label_id' => $labelsId,
                    'product_id' => $productId,
                    'store_id' => $store->getId()
                ];
            }

            if ($this->_storeManager->isSingleStoreMode()) {
                $storeId = $this->_storeManager->getDefaultStoreView()->getId();
                $where = ['label_id = ?' => $labelsId, 'store_id = ?' => $storeId];
                $connection->delete($table, $where);
                foreach ($newRecords as $productId) {
                    $data[] = [
                        'label_id' => $labelsId,
                        'product_id' => $productId,
                        'store_id' => $storeId
                    ];
                }
            }

            $connection->insertMultiple($table, $data);
        }
    }
}
