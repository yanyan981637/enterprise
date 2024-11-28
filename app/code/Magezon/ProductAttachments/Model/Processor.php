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

namespace Magezon\ProductAttachments\Model;

class Processor extends \Magezon\Core\Model\ConditionsProcessor
{
    public function process(\Magezon\ProductAttachments\Model\File $attachments)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            $store = $this->_storeManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $this->__processBystore($attachments, $store);
        } else {
            $storeIds = (array)$attachments->getData('store_id');
            if (!empty($storeIds)) {
                if (in_array(0, $storeIds)) {
                    $stores = $this->_systemStore->getStoreValuesForForm();
                    foreach ($stores as $store) {
                        if (is_array($store['value']) && !empty($store['value'])) {
                            foreach ($store['value'] as $_store) {
                                $store = $this->_storeManager->getStore($_store['value']);
                                $this->__processBystore($attachments, $store);
                            }
                        }
                    }
                } else {
                    foreach ($storeIds as $storeId) {
                        $store = $this->_storeManager->getStore($storeId);
                        $this->__processBystore($attachments, $store);
                    }
                }
            }
        }
    }

    public function __processBystore($attachments, $store)
    {
        $collection = $this->getProductByConditions($attachments, $store);
        $this->_saveTabRuleProduct($attachments, $collection, $store);
    }

    protected function _saveTabRuleProduct($attachments, $collection, $store)
    {
        $attachmentsId = $attachments->getId();
        $table = $this->_resource->getTableName('mgz_product_attachments_product');
        $connection = $this->_resource->getConnection();
        $newRecords = [];
        $items = $collection->getItems();
        foreach ($items as $_item) {
            $newRecords[] = $_item->getId();
        }
        $where = ['file_id = ?' => $attachmentsId, 'store_id = ?' => $store->getId()];
        $connection->delete($table, $where);
        if ($newRecords) {
            $data = [];
            foreach ($newRecords as $productId) {
                $data[] = [
                    'file_id' => $attachmentsId,
                    'product_id' => $productId,
                    'store_id' => $store->getId()
                ];
            }

            if ($this->_storeManager->isSingleStoreMode()) {
                $storeId = $this->_storeManager->getDefaultStoreView()->getId();
                $where = ['file_id = ?' => $attachmentsId, 'store_id = ?' => $storeId];
                $connection->delete($table, $where);
                foreach ($newRecords as $productId) {
                    $data[] = [
                        'file_id' => $attachmentsId,
                        'product_id' => $productId,
                        'store_id' => $storeId
                    ];
                }
            }

            $connection->insertMultiple($table, $data);
        }
    }
}
