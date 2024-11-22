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
 * @package   Magezon_FAQ
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\ProductAttachments\Model\ResourceModel\File\Relation\CustomerGroup;

use Magezon\ProductAttachments\Model\ResourceModel\File;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var File
     */
    protected $fileResource;

    /**
     * @param File $fileResource
     */
    public function __construct(
        File $fileResource
    ) {
        $this->fileResource = $fileResource;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $customerGroups = $this->fileResource->lookupCustomerGroupIds((int)$entity->getId());
            $entity->setData('customer_group_id', $customerGroups);
        }
        return $entity;
    }
}
