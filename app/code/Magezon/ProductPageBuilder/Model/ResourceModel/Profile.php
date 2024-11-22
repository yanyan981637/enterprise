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

namespace Magezon\ProductPageBuilder\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magezon\ProductPageBuilder\Model\ResourceModel\Profile;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Profile extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * @param Context       $context        
     * @param EntityManager $entityManager  
     * @param MetadataPool  $metadataPool   
     * @param string        $connectionName 
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgz_productpagebuilder_profile', 'profile_id');
    }

    protected function _afterLoad(AbstractModel $object)
    {
        $storeIds = $this->lookupStoreIds($object->getId());
        $object->setData('store_id', $storeIds);
        return parent::_afterLoad($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['mppbs' => $this->getTable('mgz_productpagebuilder_profile_store')], 'store_id')
            ->join(
                ['mppb' => $this->getMainTable()],
                'mppbs.profile_id = mppb.profile_id',
                []
            )
            ->where('mppb.profile_id = :profile_id');

        return $connection->fetchCol($select, ['profile_id' => (int)$id]);
    }

    public function _afterSave(AbstractModel $object)
    {
        $profileId = $object->getId();
        $table = $this->getTable('mgz_productpagebuilder_profile_store');
        if ($profileId) {
            $where = [
                'profile_id = ?' => $profileId,
            ];
            $this->getConnection()->delete($table, $where);
        }

        $storeIds = $object->getStoreId();
        if ($storeIds) {
            $data = [];
            foreach ($storeIds as $storeId) {
                $data[] = [
                    'profile_id' => $profileId,
                    'store_id' => $storeId
                ];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }
}
