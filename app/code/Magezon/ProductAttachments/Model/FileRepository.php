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
use Magezon\ProductAttachments\Api\Data\FileInterface;
use Magezon\ProductAttachments\Api\Data\FileInterfaceFactory;
use Magezon\ProductAttachments\Api\Data\FileSearchResultsInterface;
use Magezon\ProductAttachments\Api\FileRepositoryInterface;
use Magezon\ProductAttachments\Model\ResourceModel\File as ResourceFile;
use Magezon\ProductAttachments\Model\ResourceModel\File\Collection;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory as FileCollectionFactory;
use Magezon\ProductAttachments\Model\FileUploader;

class FileRepository implements FileRepositoryInterface
{
    /**
     * @var ResourceFile
     */
    protected $resource;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

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
     * @var Data\FileSearchResultsInterfaceFactory
     */
    protected $fileSearchResultsFactory;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @param ResourceFile $resource
     * @param FileFactory $fileFactory
     * @param FileInterfaceFactory $dataFileFactory
     * @param FileCollectionFactory $FileCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param Data\FileSearchResultsInterfaceFactory $fileSearchResultsFactory
     * @param \Magezon\Core\Helper\Data $coreHelper
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceFile $resource,
        FileFactory $fileFactory,
        FileInterfaceFactory $dataFileFactory,
        FileCollectionFactory $FileCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        \Magezon\ProductAttachments\Api\Data\FileSearchResultsInterfaceFactory $fileSearchResultsFactory,
        \Magezon\Core\Helper\Data $coreHelper,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->fileFactory = $fileFactory;
        $this->fileCollectionFactory = $FileCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFileFactory = $dataFileFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->fileSearchResultsFactory = $fileSearchResultsFactory;
        $this->storeManager = $storeManager;
        $this->coreHelper = $coreHelper;
    }

    /**
     * Save File data
     *
     * @param FileInterface $file
     * @return File
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(FileInterface $file)
    {
        if (empty($file->getStoreId())) {
            $file->setStoreId($this->storeManager->getStore()->getId());
        }

        try {
            $this->resource->save($file);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $file;
    }

    /**
     * Load file data by given file Identity
     *
     * @param string $fileId
     * @return File
     * @throws NoSuchEntityException
     */
    public function getById($fileId)
    {
        $file = $this->fileFactory->create();
        $file->load($fileId);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('The File with the "%1" ID doesn\'t exist.', $fileId));
        }
        $file->setName($this->coreHelper->getMediaUrl().FileUploader::BASE_PATH.$file->getName());
        return $file;
    }

    /**
     * Load category data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magezon\ProductAttachments\Api\Data\FileSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->fileSearchResultsFactory->create();
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
     * @param FileInterface $File
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FileInterface $file)
    {
        try {
            $this->resource->delete($file);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete File by given File Identity
     *
     * @param string $FileId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($FileId)
    {
        return $this->delete($this->getById($FileId));
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
            $file = $this->fileFactory->create();
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
