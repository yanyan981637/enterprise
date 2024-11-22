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
 * @copyright Copyright (C) 2021 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Model\ResourceModel\Label\Relation\CustomerGroup;

use Magezon\ProductLabels\Model\ResourceModel\Label;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Label|Form
     */
    protected $labelResource;

    /**
     * @param MetadataPool $metadataPool
     * @param Form $labelResource
     */
    public function __construct(
        MetadataPool $metadataPool,
        Label $labelResource
    ) {
        $this->metadataPool = $metadataPool;
        $this->labelResource = $labelResource;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $customerGroups = $this->labelResource->lookupCustomerGroupIds((int)$entity->getId());
            $entity->setData('customer_group_id', $customerGroups);
        }
        return $entity;
    }
}
