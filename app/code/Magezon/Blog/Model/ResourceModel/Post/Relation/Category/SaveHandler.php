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

namespace Magezon\Blog\Model\ResourceModel\Post\Relation\Category;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magezon\Blog\Api\Data\PostInterface;
use Magezon\Blog\Model\ResourceModel\Post;
use Magento\Framework\EntityManager\MetadataPool;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Post
     */
    protected $resourcePost;

    /**
     * @param MetadataPool $metadataPool
     * @param Post $resourcePost
     */
    public function __construct(
        MetadataPool $metadataPool,
        Post $resourcePost
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourcePost = $resourcePost;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->hasData('category_ids')) {
            $entityMetadata = $this->metadataPool->getMetadata(PostInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $connection = $entityMetadata->getEntityConnection();

            $oldCategories = $this->resourcePost->getCategoryIds((int)$entity->getId());
            $newCategories = (array)$entity->getData('category_ids');

            $table = $this->resourcePost->getTable('mgz_blog_category_post');

            $delete = array_diff($oldCategories, $newCategories);
            if ($delete) {
                $where = [
                    $linkField . ' = ?' => (int)$entity->getData($linkField),
                    'category_id IN (?)' => $delete,
                ];
                $connection->delete($table, $where);
            }

            $insert = array_diff($newCategories, $oldCategories);
            if ($insert) {
                $data = [];
                foreach ($insert as $categoryId) {
                    $data[] = [
                        $linkField    => (int)$entity->getData($linkField),
                        'category_id' => (int)$categoryId,
                        'position'    => 0
                    ];
                }
                $connection->insertMultiple($table, $data);
            }
        }

        return $entity;
    }
}