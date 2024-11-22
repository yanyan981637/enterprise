<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Model\ResourceModel\Label\Relation\CustomerGroup;

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
     * @var Question
     */
    protected $labelResource;

    /**
     * @param MetadataPool $metadataPool
     * @param Question         $labelResource
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
        if ($entity->hasData('customer_group_id')) {
            $entityMetadata = $this->metadataPool->getMetadata(Label::class);
            $linkField      = $entityMetadata->getLinkField();
            $connection     = $entityMetadata->getEntityConnection();
            $oldGroups      = $this->labelResource->lookupCustomerGroupIds((int)$entity->getId());
            $newGroups      = (array)$entity->getCustomerGroupId();
            $table  = $this->labelResource->getTable('mgz_productlabels_label_customergroup');
            $delete = array_diff($oldGroups, $newGroups);
            if ($delete) {
                $where = [
                    'label_id = ?' => (int)$entity->getData($linkField),
                    'customer_group_id IN (?)' => $delete
                ];
                $connection->delete($table, $where);
            }

            $insert = array_diff($newGroups, $oldGroups);
            if ($insert) {
                $data = [];
                foreach ($insert as $k => $groupId) {
                    if ($groupId !== '') {
                        $data[] = [
                            'label_id'       => (int)$entity->getData($linkField),
                            'customer_group_id' => (int)$groupId
                        ];
                    }
                }
                if (!empty($data)) {
                    $connection->insertMultiple($table, $data);
                }
            }
        }
        return $entity;
    }
}
