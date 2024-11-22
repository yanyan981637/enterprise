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

namespace Magezon\Blog\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magezon\Blog\Api\Data\AuthorInterface;

class Author extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context        
     * @param string|null                                       $connectionName
     * @param MetadataPool|null                                 $metadataPool   
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null,
        MetadataPool $metadataPool = null
    ) {
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgz_blog_author', 'author_id');
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $this->getEntityManager()->load($object, $value);
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return bool
     * @throws LocalizedException
     */
    public function checkIsUniqueUrl(AbstractModel $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(AuthorInterface::class);

        $select = $this->getConnection()->select()
            ->from(['cb' => $this->getMainTable()])
            ->where('cb.identifier = ?', $object->getData('identifier'));

        if ($object->getId()) {
            $select->where('cb.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * Process blog data before saving
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($userId = $object->getUserId()) {
            $connection = $this->getConnection();
            $bind  = ['user_id' => 0];
            $where = ['user_id = ?' => (int)$userId];
            $connection->update($this->getTable('mgz_blog_author'), $bind, $where);
        }

        if (!$this->checkIsUniqueUrl($object)) {
            throw new LocalizedException(
                __('A author URL key with the same properties already exists.')
            );
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function save(AbstractModel $object)
    {
        $this->getEntityManager()->save($object);
        return $this;
    }

    /**
     * Process author data after save author object
     * save related posts ids
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->_saveAuthorPosts($object);
        return parent::_afterSave($object);
    }

    /**
     * Delete the object
     *
     * @param AbstractModel $object
     * @return $this
     */
    public function delete(AbstractModel $object)
    {
        $this->getEntityManager()->delete($object);
        return $this;
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityManager
     * @deprecated 100.1.0
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\EntityManager::class);
        }
        return $this->entityManager;
    }

    /**
     * Get positions of associated to author posts
     *
     * @param \Magezon\Blog\Model\Category $author
     * @return array
     */
    public function getPostsPosition($author)
    {
        $list = [];
        $select = $this->getConnection()->select()->from(
            $this->getTable('mgz_blog_post')
        )->where(
            'author_id = :author_id'
        );
        $bind = ['author_id' => (int)$author->getId()];

        $result = $this->getConnection()->fetchAll($select, $bind);

        foreach ($result as $_row) {
            $list[$_row['post_id']] = 0;
        }

        return $list;
    }

    /**
     * Save author posts relation
     *
     * @param \Magezon\Blog\Model\Author $author
     * @return $this
     */
    protected function _saveAuthorPosts($author)
    {
        $id = (int)$author->getId();

        /**
         * new author-post relationships
         */
        $posts = $author->getPostedPosts();

        if ($posts === null) return $this;

        $connection = $this->getConnection();

        $ids = array_keys($posts);

        $table = $this->getTable('mgz_blog_post');
        $bind  = ['author_id' => NULL];
        $where = ['author_id = ?' => $id];
        $connection->update($table, $bind, $where);

        $bind  = ['author_id' => $id];
        $where = ['post_id IN (?)' => $ids];
        $connection->update($table, $bind, $where);

        return $this;
    }
}
