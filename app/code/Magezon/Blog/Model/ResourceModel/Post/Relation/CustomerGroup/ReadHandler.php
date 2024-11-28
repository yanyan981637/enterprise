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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Model\ResourceModel\Post\Relation\CustomerGroup;

use Magezon\Blog\Model\ResourceModel\Post;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Post
     */
    protected $postResource;

    /**
     * @param MetadataPool $metadataPool
     * @param Post $postResource
     */
    public function __construct(
        MetadataPool $metadataPool,
        Post $postResource
    ) {
        $this->metadataPool = $metadataPool;
        $this->postResource = $postResource;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $customerGroups = $this->postResource->lookupCustomerGroups((int)$entity->getId());
            $entity->setData('customer_group_id', $customerGroups);
        }
        return $entity;
    }
}
