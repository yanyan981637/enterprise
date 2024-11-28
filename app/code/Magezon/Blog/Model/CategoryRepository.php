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

namespace Magezon\Blog\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Api\CategoryRepositoryInterface;
use Magezon\Blog\Api\Data\CategoryInterface;
use Magezon\Blog\Api\Data\CategorySearchResultsInterface;
use Magezon\Blog\Api\Data\CategorySearchResultsInterfaceFactory;
use Magezon\Blog\Model\ResourceModel\Category\Collection;
use Magezon\Blog\Model\ResourceModel\Category\CollectionFactory;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var Category[]
     */
    protected $instances = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magezon\Blog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Category
     */
    protected $categoryResource;

    /**
     * @var CategorySearchResultsInterfaceFactory
     */
    protected $categorySearchResultsFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param ResourceModel\Category $categoryResource
     * @param CategorySearchResultsInterfaceFactory $categorySearchResultsFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        CollectionFactory $categoryCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Category $categoryResource,
        CategorySearchResultsInterfaceFactory $categorySearchResultsFactory
    ) {
        $this->storeManager                 = $storeManager;
        $this->categoryFactory              = $categoryFactory;
        $this->categoryCollectionFactory    = $categoryCollectionFactory;
        $this->categoryResource             = $categoryResource;
        $this->categorySearchResultsFactory = $categorySearchResultsFactory;
    }

    /**
     * Save category.
     *
     * @param CategoryInterface $category
     * @return CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CategoryInterface $category)
    {
        $storeId = $category->getStoreId();
        if (!$storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        if ($category->getId()) {
            $newData    = $category->getData();
            $category = $this->get($category->getId(), $storeId);
            foreach ($newData as $k => $v) {
                $category->setData($k, $v);
            }
        }

        try {
            $this->categoryResource->save($category);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save category: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        unset($this->instances[$category->getId()]);
        return $this->get($category->getId(), $storeId);
    }

    /**
     * @param $categoryId
     * @param $storeId
     * @return CategoryInterface|mixed|null
     * @throws NoSuchEntityException
     */
    public function get($categoryId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$categoryId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }
            $category->load($categoryId);

            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $categoryId);
            }
            $this->instances[$categoryId][$cacheKey] = $category;
        }
        return $this->instances[$categoryId][$cacheKey];
    }

    /**
     * @param CategoryInterface $category
     * @return true
     * @throws StateException
     */
    public function delete(CategoryInterface $category)
    {
        try {
            $categoryId = $category->getId();
            $this->categoryResource->delete($category);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete category with id %1',
                    $category->getId()
                ),
                $e
            );
        }
        unset($this->instances[$categoryId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($categoryId)
    {
        $category = $this->get($categoryId);
        return  $this->delete($category);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return CategorySearchResultsInterface
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->categorySearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->categoryCollectionFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $categorys = [];

        foreach ($collection as $category) {
            $categorys[] = $this->get($category->getId());
        }
        $searchResults->setItems($categorys);
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), $filter->getValue());
        }
    }
}
