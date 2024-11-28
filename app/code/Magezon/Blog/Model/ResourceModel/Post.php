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

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Api\Data\PostInterface;

class Post extends AbstractDb
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Config
     */
    private $_catalogConfig;

    /**
     * @var CollectionFactory
     */
    private $_productCollectionFactory;

    /**
     * @var Tag\CollectionFactory
     */
    private $_tagCollectionFactory;

    /**
     * @var Category\CollectionFactory
     */
    private $_categoryCollectionFactory;

    /**
     * @var Post\CollectionFactory
     */
    private $_postCollectionFactory;

    /**
     * @var Author\CollectionFactory
     */
    private $_authorCollectionFactory;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param Config $catalogConfig
     * @param CollectionFactory $productCollectionFactory
     * @param Tag\CollectionFactory $tagCollectionFactory
     * @param Category\CollectionFactory $categoryCollectionFactory
     * @param Post\CollectionFactory $postCollectionFactory
     * @param Author\CollectionFactory $authorCollectionFactory,
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        Config $catalogConfig,
        CollectionFactory $productCollectionFactory,
        Tag\CollectionFactory $tagCollectionFactory,
        Category\CollectionFactory $categoryCollectionFactory,
        Post\CollectionFactory $postCollectionFactory,
        Author\CollectionFactory $authorCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->_catalogConfig = $catalogConfig;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_tagCollectionFactory = $tagCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_postCollectionFactory = $postCollectionFactory;
        $this->_authorCollectionFactory = $authorCollectionFactory;
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgz_blog_post', 'post_id');
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
     * Process blog data before saving
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $urlKey = $object->getIdentifier();
        if ($urlKey) {
            $object->setIdentifier(strtolower($urlKey));
        }

        if (!$object->getData('publish_date')) {
            if ($object->getData('creation_time')) {
                $object->setData('publish_date', $object->getData('creation_time'));
            } else {
                $object->setData('publish_date', date('Y-m-d H:i:s', strtotime('now')));
            }
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
                __('A blog URL key with the same properties already exists in the selected store.')
            );
        }

        if (!$this->isValidPostUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The blog URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericPostUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The blog URL key cannot be made of only numbers.')
            );
        }

        $object->setUpdateTime(null);

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
        $entityMetadata = $this->metadataPool->getMetadata(PostInterface::class);
        $linkField = $entityMetadata->getLinkField();

        if ($this->_storeManager->isSingleStoreMode()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array) $object->getData('store_id');
        }

        $select = $this->getConnection()->select()
            ->from(['cb' => $this->getMainTable()])
            ->join(
                ['cbs' => $this->getTable('mgz_blog_post_store')],
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
     *  Check whether blog url key is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericPostUrlKey(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether blog url key is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidPostUrlKey(AbstractModel $object)
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
        $this->_savePostProducts($object);
        $this->_savePostPosts($object);
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
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(PostInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cbs' => $this->getTable('mgz_blog_post_store')], 'store_id')
            ->join(
                ['cb' => $this->getMainTable()],
                'cbs.' . $linkField . ' = cb.' . $linkField,
                []
            )
            ->where('cb.' . $entityMetadata->getIdentifierField() . ' = :post_id');

        return $connection->fetchCol($select, ['post_id' => (int) $id]);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function getCategoryIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(PostInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cbs' => $this->getTable('mgz_blog_category_post')], 'category_id')
            ->join(
                ['cb' => $this->getMainTable()],
                'cbs.' . $linkField . ' = cb.' . $linkField,
                []
            )
            ->where('cb.' . $entityMetadata->getIdentifierField() . ' = :post_id');

        return $connection->fetchCol($select, ['post_id' => (int) $id]);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function getTagIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(PostInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cbs' => $this->getTable('mgz_blog_tag_post')], 'tag_id')
            ->join(
                ['cb' => $this->getMainTable()],
                'cbs.' . $linkField . ' = cb.' . $linkField,
                []
            )
            ->where('cb.' . $entityMetadata->getIdentifierField() . ' = :post_id');

        return $connection->fetchCol($select, ['post_id' => (int) $id]);
    }

    /**
     * Get customer groups to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupCustomerGroups($postId)
    {
        $connection = $this->getConnection();
        $entityMetadata = $this->metadataPool->getMetadata(PostInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cps' => $this->getTable('mgz_blog_post_customer_group')], 'customer_group_id')
            ->join(
                ['cp' => $this->getMainTable()],
                'cps.post_id = cp.' . $linkField,
                []
            )
            ->where('cp.' . $entityMetadata->getIdentifierField() . ' = :post_id');

        return $connection->fetchCol($select, ['post_id' => (int) $postId]);
    }

    /**
     * Get collection of post products
     *
     * @param \Magezon\Blog\Model\Post $post
     * @return Collection
     */
    public function getProductCollection($post)
    {
        /** @var Collection $collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->joinField(
            'post_id',
            'mgz_blog_post_product',
            'post_id',
            'product_id = entity_id',
            null
        )->addFieldToFilter(
            'post_id',
            (int) $post->getId()
        );

        $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addUrlRewrite();

        return $collection;
    }

    /**
     * Get collection of post tags
     *
     * @param \Magezon\Blog\Model\Post $post
     * @return Tag\Collection
     */
    public function getTagCollection($post)
    {
        /** @var Tag\Collection $collection */
        $collection = $this->_tagCollectionFactory->create();
        $collection->addIsActiveFilter();
        $collection->getSelect()->joinLeft(
            ['mbtp' => $this->getTable('mgz_blog_tag_post')],
            'main_table.tag_id = mbtp.tag_id',
            []
        )->where(
            'mbtp.post_id = ?', (int) $post->getId()
        )->group('main_table.tag_id');
        return $collection;
    }

    /**
     * @param $post
     * @return DataObject[]
     */
    public function getCategoryList($post)
    {
        /** @var Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['mbcp' => $this->getTable('mgz_blog_category_post')],
            'main_table.category_id = mbcp.category_id',
            []
        )->where(
            'mbcp.post_id = ?', (int) $post->getId()
        )->group('main_table.category_id');
        $collection->addFieldToFilter('is_active', \Magezon\Blog\Model\Category::STATUS_ENABLED);
        return array_values($collection->getItems());
    }

    /**
     * Post product table name getter
     *
     * @return string
     */
    public function getPostProductTable()
    {
        return $this->getTable('mgz_blog_post_product');
    }

    /**
     * Get positions of associated to post posts
     *
     * @param \Magezon\Blog\Model\Post $post
     * @return array
     */
    public function getProductsPosition($post)
    {
        $select = $this->getConnection()->select()->from(
            $this->getPostProductTable(),
            ['product_id', 'position']
        )->where(
            'post_id = :post_id'
        );
        $bind = ['post_id' => (int) $post->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Post post table name getter
     *
     * @return string
     */
    public function getPostPostTable()
    {
        return $this->getTable('mgz_blog_post_post');
    }

    /**
     * Get positions of associated to post posts
     *
     * @param \Magezon\Blog\Model\Post $post
     * @return array
     */
    public function getPostsPosition($post)
    {
        $select = $this->getConnection()->select()->from(
            $this->getPostPostTable(),
            ['post_id', 'position']
        )->where(
            'entity_id = :entity_id'
        );
        $bind = ['entity_id' => (int) $post->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Save post products relation
     *
     * @param \Magezon\Blog\Model\Post $post
     * @return $this
     */
    protected function _savePostProducts($post)
    {
        $id = $post->getId();

        /**
         * new post-product relationships
         */
        $products = $post->getPostedProducts();

        /**
         * Example re-save post
         */
        if ($products === null) {
            $products = $post->getPostedProductsModel();
        }

        /**
         * old post-product relationships
         */
        $oldProducts = $post->getProductsPosition();

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldProducts array)
         */
        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $connection = $this->getConnection();

        /**
         * Delete products from post
         */
        if (!empty($delete)) {
            $cond = ['product_id IN(?)' => array_keys($delete), 'post_id=?' => $id];
            $connection->delete($this->getPostProductTable(), $cond);
        }

        /**
         * Add products to post
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $position) {
                $data[] = [
                    'post_id' => (int) $id,
                    'product_id' => (int) $productId,
                    'position' => (int) $position,
                ];
            }
            $connection->insertMultiple($this->getPostProductTable(), $data);
        }

        /**
         * Update product positions in post
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $productId => $position) {
                $delta = $position - $oldProducts[$productId];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $productId;
            }

            foreach ($newPositions as $delta => $productIds) {
                $bind = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['post_id = ?' => (int) $id, 'product_id IN (?)' => $productIds];
                $connection->update($this->getPostProductTable(), $bind, $where);
            }
        }

        return $this;
    }

    /**
     * Save post posts relation
     *
     * @param \Magezon\Blog\Model\Post $post
     * @return $this
     */
    protected function _savePostPosts($post)
    {
        $id = $post->getId();

        /**
         * new post-post relationships
         */
        $posts = $post->getPostedPosts();

        /**
         * Example re-save post
         */
        if ($posts === null) {
            $posts = $post->getPostedPostsModel();
        }

        /**
         * old post-post relationships
         */
        $oldPosts = $post->getPostsPosition();
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
         * Delete posts from post
         */
        if (!empty($delete)) {
            $cond = ['post_id IN(?)' => array_keys($delete), 'entity_id=?' => $id];
            $connection->delete($this->getPostPostTable(), $cond);
        }

        /**
         * Add posts to post
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'entity_id' => (int) $id,
                    'post_id' => (int) $postId,
                    'position' => (int) $position,
                ];
            }
            $connection->insertMultiple($this->getPostPostTable(), $data);
        }

        /**
         * Update post positions in post
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
                $bind = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['entity_id = ?' => (int) $id, 'post_id IN (?)' => $postIds];
                $connection->update($this->getPostPostTable(), $bind, $where);
            }
        }

        return $this;
    }

    /**
     * Get collection of related products
     *
     * @param \Magezon\Blog\Model\Post $post
     * @return Post\Collection
     */
    public function getRelatedPostCollection($post)
    {
        $collection = $this->_postCollectionFactory->create();
        $collection->prepareCollection();
        $collection->getSelect()->joinLeft(
            ['mbpp' => $this->getTable('mgz_blog_post_post')],
            'main_table.post_id = mbpp.post_id',
            []
        )->where('mbpp.entity_id = ?', $post->getId())->group('main_table.post_id')->order('position', 'ASC');
        return $collection;
    }

    /**
     * @param $post
     * @param $optionValue
     * @return array|void
     */
    public function getNextAndPrevPost($post, $optionValue)
    {
        $postIdCurrent = $post->getId();
        $authorIdCurrent = $post->getAuthorId();
        $collection = $this->_postCollectionFactory->create();
        $collection->addFieldToSelect(['title', 'image', 'identifier']);
        switch ($optionValue) {
            case '1':
                $collection->prepareCollection();
                $collection->addFieldToFilter('post_id', ['lt' => $postIdCurrent]);
                $collection->setOrder('post_id', 'DESC');
                $previousItem = $collection->getFirstItem();
                $collection->clear()->getSelect()->reset(Select::WHERE);
                $collection->addCategoryCollection()
                    ->addFieldToFilter('end_date', [
                        ['gt' => date("Y-m-d H:i:s")],
                        ['null' => true],
                    ])
                    ->addFieldToFilter('publish_date', [
                        ['lteq' => date("Y-m-d H:i:s")]
                    ])
                    ->addFieldToFilter('post_id', ['gt' => $postIdCurrent]);
                    $nextItem = $collection->getLastItem();
                return [
                    'prev' => $previousItem,
                    'next' => $nextItem,
                ];
            case '2':
                $collection->prepareCollection();
                $collection->addFieldToFilter('author_id', $authorIdCurrent);
                $collection->addFieldToFilter('post_id', ['lt' => $postIdCurrent]);
                $collection->setOrder('post_id', 'DESC');
                $previousItem = $collection->getFirstItem();
                $collection->clear()->getSelect()->reset(Select::WHERE);
                $collection->addCategoryCollection()
                    ->addFieldToFilter('end_date', [
                        ['gt' => date("Y-m-d H:i:s")],
                        ['null' => true],
                    ])
                    ->addFieldToFilter('publish_date', [
                        ['lteq' => date("Y-m-d H:i:s")]
                    ])
                    ->addFieldToFilter('author_id', $authorIdCurrent)
                    ->addFieldToFilter('post_id', ['gt' => $postIdCurrent]);
                $nextItem = $collection->getLastItem();
                return [
                    'prev' => $previousItem,
                    'next' => $nextItem,
                ];
            default:
                $collection->prepareCollection();
                $collection->getSelect()->joinLeft(
                    ['mbcp' => $this->getTable('mgz_blog_category_post')],
                    'main_table.post_id = mbcp.post_id',
                    []
                )->where(
                    'mbcp.category_id IN (?)', $this->getCategoryIds($postIdCurrent)
                );
                $collection->addFieldToFilter('post_id', ['lt' => $postIdCurrent]);
                $collection->setOrder('post_id', 'DESC');
                $previousItem = $collection->getFirstItem();
                $previousItem = $collection->getLastItem();
                $collection->clear()->getSelect()->reset(Select::WHERE);
                $collection->addCategoryCollection()
                    ->addFieldToFilter('end_date', [
                        ['gt' => date("Y-m-d H:i:s")],
                        ['null' => true],
                    ])
                    ->addFieldToFilter('publish_date', [
                        ['lteq' => date("Y-m-d H:i:s")]
                    ])
                    ->addFieldToFilter('post_id', ['gt' => $postIdCurrent])
                    ->getSelect()->where('mbcp.category_id IN (?)', $this->getCategoryIds($postIdCurrent));
                $nextItem = $collection->getLastItem();
                return [
                    'prev' => $previousItem,
                    'next' => $nextItem,
                ];
        }
    }

    /**
     * @param array $categories
     * @return bool
     */
    public function issetCategorires($categories)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $categoriesId = $collection->getAllIds();
        $diff = array_diff($categories, $categoriesId);
        if (empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $tags
     * @return bool
     */
    public function issetTags($tags)
    {
        $collection = $this->_tagCollectionFactory->create();
        $tagsId = $collection->getAllIds();
        $diff = array_diff($tags, $tagsId);
        if (empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * @param $author
     * @return bool
     */
    public function issetAuthor($author)
    {
        $collection = $this->_authorCollectionFactory->create();
        $authorsId = $collection->getAllIds();
        if (in_array($author, $authorsId)) {
            return true;
        }
        return false;
    }
}
