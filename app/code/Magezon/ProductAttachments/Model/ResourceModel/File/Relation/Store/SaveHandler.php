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

namespace Magezon\ProductAttachments\Model\ResourceModel\File\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magezon\ProductAttachments\Api\Data\FileInterface;
use Magezon\ProductAttachments\Model\ResourceModel\File;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var File
     */
    protected $resourceFile;

    /**
     * @param MetadataPool $metadataPool
     * @param File $resourceFile
     */
    public function __construct(
        MetadataPool $metadataPool,
        File $resourceFile
    ) {
        $this->metadataPool     = $metadataPool;
        $this->resourceFile = $resourceFile;
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
            $entityMetadata = $this->metadataPool->getMetadata(FileInterface::class);
            $linkField      = $entityMetadata->getLinkField();
            $connection     = $entityMetadata->getEntityConnection();
            $newStores      = (array)$entity->getStoreId();
            $table  = $this->resourceFile->getTable('mgz_product_attachments_store');
            $where = [
                'file_id = ?' => (int)$entity->getData($linkField),
            ];
            $connection->delete($table, $where);

            $data = [];
            foreach ($newStores as $k => $storeId) {
                $data[] = [
                    'file_id'       => (int)$entity->getData($linkField),
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
