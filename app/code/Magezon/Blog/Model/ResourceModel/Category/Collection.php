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

namespace Magezon\Blog\Model\ResourceModel\Category;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magezon\Blog\Api\Data\CategoryInterface;
use Magezon\Blog\Model\ResourceModel\AbstractCollection;
use Magezon\Blog\Model\ResourceModel\Category;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'category_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_category_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'category_collection';

    protected function _construct()
    {
        $this->_init(\Magezon\Blog\Model\Category::class, Category::class);
        $this->_map['fields']['category_id'] = 'main_table.category_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * @param $store
     * @param $withAdmin
     * @return $this|Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(CategoryInterface::class);

        $this->performAfterLoad('mgz_blog_category_store', $entityMetadata->getLinkField());

        return parent::_afterLoad();
    }

    /**
     * @param $storeId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function prepareCollection($storeId = Store::DEFAULT_STORE_ID)
    {
        $store = $this->storeManager->getStore($storeId);
        $this->addIsActiveFilter()->addStoreFilter($store);
        return $this;
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('is_active', (int)$isActive ? 1 : 0);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('mgz_blog_category_store', 'category_id');
    }

    /**
     * @return $this
     */
    public function addTotalPosts()
    {
        $this->getSelect()->joinLeft(
            ['mbcp' => $this->getTable('mgz_blog_category_post')],
            'main_table.category_id = mbcp.category_id',
            ['total_posts' => 'COUNT(mbcp.category_id)']
        )->group('main_table.category_id');
        return $this;
    }
}