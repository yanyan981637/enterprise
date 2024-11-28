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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\ProductAttachments\Api\Data;
use Magezon\ProductAttachments\Api\Data\CategoryInterface;
use Magezon\ProductAttachments\Api\Data\CategoryInterfaceFactory;
use Magezon\ProductAttachments\Api\Data\CategorySearchResultsInterface;
use Magezon\ProductAttachments\Api\CategoryRepositoryInterface;
use Magezon\ProductAttachments\Model\ResourceModel\Category as ResourceCategory;
use Magezon\ProductAttachments\Model\ResourceModel\File\Collection;
use Magezon\ProductAttachments\Model\ResourceModel\Category\CollectionFactory as FileCollectionFactory;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var ResourceCategory
     */
    protected $resource;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var FileCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var Data\FileSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var FileInterfaceFactory
     */
    protected $dataFileFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data\CategorySearchResultsInterfaceFactory
     */
    protected $categorySearchResultsFactory;


    /**
     * CategoryRepository constructor.
     * @param ResourceCategory $resource
     * @param CategoryFactory $fileFactory
     * @param CategoryInterfaceFactory $dataFileFactory
     * @param FileCollectionFactory $FileCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param Data\CategorySearchResultsInterfaceFactory $categorySearchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceCategory $resource,
        CategoryFactory $categoryFactory,
        CategoryInterfaceFactory $dataFileFactory,
        FileCollectionFactory $FileCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        \Magezon\ProductAttachments\Api\Data\CategorySearchResultsInterfaceFactory $categorySearchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->categoryFactory = $categoryFactory;
        $this->fileCollectionFactory = $FileCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFileFactory = $dataFileFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->categorySearchResultsFactory = $categorySearchResultsFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Save category data
     *
     * @param CategoryInterface $category
     * @return Category
     * @throws CouldNotSaveException
     */
    public function save(CategoryInterface $category)
    {
        try {
            $this->resource->save($category);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $category;
    }

    /**
     * Load category data by given category Identity
     *
     * @param $categoryId
     * @return Category
     * @throws NoSuchEntityException
     */
    public function getById($categoryId)
    {
        $catetory = $this->categoryFactory->create();
        $catetory->load($categoryId);
        if (!$catetory->getId()) {
            throw new NoSuchEntityException(__('The category with the "%1" ID doesn\'t exist.', $categoryId));
        }
        return $catetory;
    }

    /**
     * Load category data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magezon\ProductAttachments\Api\Data\CategorySearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->categorySearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magezon\ProductAttachments\Model\ResourceModel\File\Collection $collection */
        $collection = $this->fileCollectionFactory->create();

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
        $files = [];

        foreach ($collection as $file) {
            $files[] = $this->get($file->getId());
        }
        $searchResults->setItems($files);
        return $searchResults;
    }

    /**
     * Delete File
     *
     * @param CategoryInterface $category
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CategoryInterface $category)
    {
        try {
            $this->resource->delete($category);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete File by given File Identity
     *
     * @param string $categoryId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($categoryId)
    {
        return $this->delete($this->getById($categoryId));
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magezon\ProductAttachments\Model\ResourceModel\File\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magezon\ProductAttachments\Model\ResourceModel\File\Collection $collection
    ) {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), $filter->getValue());
        }
    }

    /**
     * Retrieve category.
     *
     * @param int $fileId
     * @param int $storeId
     * @return \Magezon\ProductAttachments\Api\Data\FileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($fileId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$fileId][$cacheKey])) {
            /** @var File $file */
            $file = $this->categoryFactory->create();
            if (null !== $storeId) {
                $file->setStoreId($storeId);
            }
            $file->load($fileId);

            if (!$file->getId()) {
                throw NoSuchEntityException::singleField('id', $fileId);
            }
            $this->instances[$fileId][$cacheKey] = $file;
        }
        return $this->instances[$fileId][$cacheKey];
    }
}
