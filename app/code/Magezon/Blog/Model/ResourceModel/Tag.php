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
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magezon\Blog\Api\Data\TagInterface;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class Tag extends AbstractDb
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
     * @var CollectionFactory
     */
    protected $_postCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $postCollectionFactory
     * @param MetadataPool $metadataPool
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        CollectionFactory $postCollectionFactory,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->_postCollectionFactory = $postCollectionFactory;
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgz_blog_tag', 'tag_id');
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
     * @return $this|Tag
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $urlKey = $object->getIdentifier();
        if ($urlKey) {
            $object->setIdentifier(strtolower($urlKey));
        }

        if (!$object->getId() && !$object->getIdentifier() && $object->getTitle()) {
            $urlKey = $object->formatUrlKey($object->getTitle());
            $object->setIdentifier($urlKey);
        }

        if ($urlKey = $object->getIdentifier()) {
            $object->setIdentifier($object->formatUrlKey($urlKey));
        }

        if (!$this->checkIsUniqueUrl($object)) {
            throw new LocalizedException(
                __('A tag URL key with the same properties already exists.')
            );
        }

        if (!$this->isValidTagUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The tag URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericTagUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The tag URL key cannot be made of only numbers.')
            );
        }

        return $this;
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param AbstractModel $object
     * @return bool
     * @throws LocalizedException
     */
    public function checkIsUniqueUrl(AbstractModel $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(TagInterface::class);

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
     *  Check whether blog url key is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericTagUrlKey(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether blog url key is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidTagUrlKey(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
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
     * Process tag data after save tag object
     * save related posts ids
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->_saveTagPosts($object);
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
     * @return EntityManager|mixed
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = ObjectManager::getInstance()
                ->get(EntityManager::class);
        }
        return $this->entityManager;
    }

    /**
     * Tag post table name getter
     *
     * @return string
     */
    public function getTagPostTable()
    {
        return $this->getTable('mgz_blog_tag_post');
    }

    /**
     * Get collection of tag posts
     *
     * @param \Magezon\Blog\Model\Tag $tag
     * @return Collection
     */
    public function getPostCollection($tag)
    {
        /** @var Collection $collection */
        $collection = $this->_postCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['mbtp' => $this->getTable('mgz_blog_tag_post')],
            'main_table.post_id = mbtp.post_id',
            []
        )->group('main_table.post_id');
        $collection->addFieldToFilter(
            'mbtp.tag_id',
            (int) $tag->getId()
        );
        $collection->prepareCollection();
        $collection->setOrder('pinned', 'DESC');
        $collection->setOrder('publish_date', 'DESC');
        return $collection;
    }

    /**
     * Save tag posts relation
     *
     * @param \Magezon\Blog\Model\Tag $tag
     * @return $this
     */
    protected function _saveTagPosts($tag)
    {
        $id = $tag->getId();

        /**
         * new tag-post relationships
         */
        $posts = $tag->getPostedPosts();

        /**
         * Example re-save tag
         */
        if ($posts === null) {
            return $this;
        }

        /**
         * old tag-post relationships
         */
        $oldPosts = $tag->getPostsPosition();

        $insert = array_diff_key($posts, $oldPosts);
        $delete = array_diff_key($oldPosts, $posts);

        /**
         * Find post ids which are presented in both arrays
         * and saved before (check $oldPosts array)
         */
        $update = array_intersect_key($posts, $oldPosts);
        $update = array_diff_assoc($update, $oldPosts);

        $connection = $this->getConnection();

        /**
         * Delete posts from tag
         */
        if (!empty($delete)) {
            $cond = ['post_id IN(?)' => array_keys($delete), 'tag_id=?' => $id];
            $connection->delete($this->getTagPostTable(), $cond);
        }

        /**
         * Add posts to tag
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'tag_id' => (int) $id,
                    'post_id' => (int) $postId,
                ];
            }
            $connection->insertMultiple($this->getTagPostTable(), $data);
        }

        return $this;
    }
}
