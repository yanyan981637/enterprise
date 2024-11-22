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

namespace Magezon\Blog\Model\ResourceModel\Post\Relation\Tag;

use Magezon\Blog\Model\ResourceModel\Post;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var Post
     */
    protected $resourcePost;

    /**
     * @param Post $resourcePost
     */
    public function __construct(
        Post $resourcePost
    ) {
        $this->resourcePost = $resourcePost;
    }

    /**
     * @param $entity
     * @param $arguments
     * @return bool|object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $ids = $this->resourcePost->getTagIds((int)$entity->getId());
            $entity->setData('tag_ids', $ids);
        }
        return $entity;
    }
}