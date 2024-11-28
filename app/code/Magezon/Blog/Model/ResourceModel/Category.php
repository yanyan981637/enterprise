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
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magezon\Blog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Model\Config\Source\PostsSortBy;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class Category extends AbstractDb
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
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Sort By Publish date
     */
    const POSTS_SORT_FIELD_BY_PUBLISH_DATE = 'publish_date';

    /**
     * Sort By Position
     */
    const POSTS_SORT_FIELD_BY_POSITION = 'position';

    /**
     * Sort By Title
     */
    const POSTS_SORT_FIELD_BY_TITLE = 'title';

    /**
     * @var CollectionFactory
     */
    protected $_postCollectionFactory;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param CollectionFactory $postCollectionFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        CollectionFactory $postCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
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
        $this->_init('mgz_blog_category', 'category_id');
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
     * @return $this|Category
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $urlKey = $object->getIdentifier();
        if ($urlKey) $object->setIdentifier(strtolower($urlKey));

        if (!$object->getId() && !$object->getIdentifier() && $object->getTitle()) {
            $urlKey = $object->formatUrlKey($object->getTitle());
            $object->setIdentifier($urlKey);
        }

        if ($urlKey = $object->getIdentifier()) {
            $object->setIdentifier($object->formatUrlKey($urlKey));
        }

        if (!$this->checkIsUniqueUrl($object)) {
            throw new LocalizedException(
                __('A category URL key with the same properties already exists in the selected store.')
            );
        }

        if (!$this->isValidCategoryUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The category URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericCategoryUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The category URL key cannot be made of only numbers.')
            );
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return bool
     * @throws LocalizedException
     */
    public function checkIsUniqueUrl(AbstractModel $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $linkField = $entityMetadata->getLinkField();

        if ($this->_storeManager->isSingleStoreMode()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('store_id');
        }

        $select = $this->getConnection()->select()
            ->from(['cb' => $this->getMainTable()])
            ->join(
                ['cbs' => $this->getTable('mgz_blog_category_store')],
                'cb.' . $linkField . ' = cbs.' . $linkField,
                []
            )
            ->where('cb.identifier = ?', $object->getData('identifier'))
            ->where('cbs.store_id IN (?)', $stores);

        if ($object->getId()) {
            $select->where('cb.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     *  Check whether category url key is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericCategoryUrlKey(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether category url key is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidCategoryUrlKey(AbstractModel $object)
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
     * Process category data after save category object
     * save related posts ids
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->_saveCategoryPosts($object);
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
     * @return EntityManager
     * @deprecated 100.1.0
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
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cbs' => $this->getTable('mgz_blog_category_store')], 'store_id')
            ->join(
                ['cb' => $this->getMainTable()],
                'cbs.' . $linkField . ' = cb.' . $linkField,
                []
            )
            ->where('cb.' . $entityMetadata->getIdentifierField() . ' = :category_id');

        return $connection->fetchCol($select, ['category_id' => (int)$id]);
    }

    /**
     * Get collection of category posts
     *
     * @param \Magezon\Blog\Model\Category $category
     * @return Collection
     */
    public function getPostCollection($category)
    {
        /** @var Collection $collection */
        $collection = $this->_postCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['mbcp' => $this->getTable('mgz_blog_category_post')],
            'main_table.post_id = mbcp.post_id',
            []
        )->group('main_table.post_id');
        $collection->prepareCollection();
        $collection->addCategoryCollection();
        $collection->addAuthorToCollection();
        $collection->addTotalComments();
        $collection->addFieldToFilter(
            'mbcp.category_id',
            (int)$category->getId()
        );
        $collection->setOrder('pinned', 'DESC');

        if($this->getCollectionOrderField($category) == self::POSTS_SORT_FIELD_BY_PUBLISH_DATE) {
            $collection->setOrder($this->getCollectionOrderField($category), 'DESC');
        }else{
            $collection->setOrder($this->getCollectionOrderField($category), 'ASC');
        }
        return $collection;
    }

    /**
     * @param $category
     * @return string
     */
    public function getCollectionOrderField($category)
    {
        $postsSortBy = $category->getPostsSortBy();
        switch ($postsSortBy) {
            case PostsSortBy::POSITION:
                return self::POSTS_SORT_FIELD_BY_POSITION;
            case PostsSortBy::TITLE:
                return self::POSTS_SORT_FIELD_BY_TITLE;
            default:
                return self::POSTS_SORT_FIELD_BY_PUBLISH_DATE;
        }
    }

    /**
     * Category post table name getter
     *
     * @return string
     */
    public function getCategoryPostTable()
    {
        return $this->getTable('mgz_blog_category_post');
    }

    /**
     * Get positions of associated to category posts
     *
     * @param \Magezon\Blog\Model\Category $category
     * @return array
     */
    public function getPostsPosition($category)
    {
        $select = $this->getConnection()->select()->from(
            $this->getCategoryPostTable(),
            ['post_id', 'position']
        )->where(
            'category_id = :category_id'
        );
        $bind = ['category_id' => (int)$category->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Save category posts relation
     *
     * @param \Magezon\Blog\Model\Category $category
     * @return $this
     */
    protected function _saveCategoryPosts($category)
    {
        $id = $category->getId();

        /**
         * new category-post relationships
         */
        $posts = $category->getPostedPosts();

        /**
         * Example re-save category
         */
        if ($posts === null) return $this;

        /**
         * old category-post relationships
         */
        $oldPosts = $category->getPostsPosition();

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
         * Delete posts from category
         */
        if (!empty($delete)) {
            $cond = ['post_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $connection->delete($this->getCategoryPostTable(), $cond);
        }

        /**
         * Add posts to category
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'post_id'     => (int)$postId,
                    'position'    => (int)$position
                ];
            }
            $connection->insertMultiple($this->getCategoryPostTable(), $data);
        }

        /**
         * Update post positions in category
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $postId => $position) {
                $delta = $position - $oldPosts[$postId];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $postId;
            }

            foreach ($newPositions as $delta => $postIds) {
                $bind  = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['category_id = ?' => (int)$id, 'post_id IN (?)' => $postIds];
                $connection->update($this->getCategoryPostTable(), $bind, $where);
            }
        }

        return $this;
    }

    /**
     * Get posts count in category
     *
     * @param \Magezon\Blog\Model\Category $category
     * @return int
     */
    public function getPostCount($category)
    {
        return $this->getPostCollection($category)->count();
    }
}
