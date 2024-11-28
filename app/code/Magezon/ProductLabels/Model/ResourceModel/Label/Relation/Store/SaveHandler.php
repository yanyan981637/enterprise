<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @author    Hoang PB - hoangpb@magezon.com
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductLabels\Model\ResourceModel\Label\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magezon\ProductLabels\Model\Label;
use Magezon\ProductLabels\Model\ResourceModel\Label as LabelResource;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Label
     */
    protected $labelResource;

    /**
     * @param MetadataPool $metadataPool    
     * @param Label      $labelResource 
     */
    public function __construct(
        MetadataPool $metadataPool,
        LabelResource $labelResource
    ) {
        $this->metadataPool  = $metadataPool;
        $this->labelResource = $labelResource;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->hasData('store_id')) {
            $entityMetadata = $this->metadataPool->getMetadata(\Magezon\ProductLabels\Model\Label::class);
            $linkField      = $entityMetadata->getLinkField();
            $connection     = $entityMetadata->getEntityConnection();
            $newStores      = (array)$entity->getStoreId();
            $table  = $this->labelResource->getTable('mgz_productlabels_label_store');
            $where = [
                'label_id = ?' => (int)$entity->getData($linkField),
            ];
            $connection->delete($table, $where);

            $data = [];
            foreach ($newStores as $k => $storeId) {
                $data[] = [
                    'label_id'       => (int)$entity->getData($linkField),
                    'store_id' => (int)$storeId
                ];
            }
            if (!empty($data)) {
                $connection->insertMultiple($table, $data);
            }
        }
        return $entity;
    }
}
