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
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magezon\ProductAttachments\Api\Data;
use Magezon\ProductAttachments\Api\Data\IconInterface;
use Magezon\ProductAttachments\Api\Data\CategorySearchResultsInterface;
use Magezon\ProductAttachments\Api\IconRepositoryInterface;
use Magezon\ProductAttachments\Model\ResourceModel\Icon as ResourceIcon;
use Magezon\ProductAttachments\Model\ResourceModel\File\Collection;
use Magezon\ProductAttachments\Model\ResourceModel\Icon\CollectionFactory as IconCollectionFactory;
use Magezon\ProductAttachments\Model\IconUploader;

class IconRepository implements IconRepositoryInterface
{
    /**
     * @var ResourceIcon
     */
    protected $resource;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var IconCollectionFactory
     */
    protected $iconCollectionFactory;

    /**
     * @var Data\FileSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Data\IconSearchResultsInterfaceFactory
     */
    protected $iconSearchResultsFactory;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * IconRepository constructor.
     * @param ResourceIcon $resource
     * @param IconFactory $iconFactory
     * @param IconCollectionFactory $iconCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magezon\Core\Helper\Data $coreHelper
     * @param Data\IconSearchResultsInterfaceFactory $iconSearchResultsFactory
     */
    public function __construct(
        ResourceIcon $resource,
        IconFactory $iconFactory,
        IconCollectionFactory $iconCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        \Magezon\Core\Helper\Data $coreHelper,
        \Magezon\ProductAttachments\Api\Data\IconSearchResultsInterfaceFactory $iconSearchResultsFactory
    ) {
        $this->resource = $resource;
        $this->iconFactory = $iconFactory;
        $this->iconCollectionFactory = $iconCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->iconSearchResultsFactory = $iconSearchResultsFactory;
        $this->coreHelper = $coreHelper;
    }

    /**
     * Save icon data
     *
     * @param IconInterface $icon
     * @return Icon
     * @throws CouldNotSaveException
     */
    public function save(IconInterface $icon)
    {
        try {
            $this->resource->save($icon);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $icon;
    }

    /**
     * Load icon data by given icon Identity
     *
     * @param $iconId
     * @return Category
     * @throws NoSuchEntityException
     */
    public function getById($iconId)
    {
        $icon = $this->iconFactory->create();
        $icon->load($iconId);
        if (!$icon->getId()) {
            throw new NoSuchEntityException(__('The icon with the "%1" ID doesn\'t exist.', $iconId));
        }
        $icon->setFileName($this->coreHelper->getMediaUrl().IconUploader::BASE_PATH.$icon->getFileName());
        return $icon;
    }

    /**
     * Load icon data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magezon\ProductAttachments\Api\Data\CategorySearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->iconSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magezon\ProductAttachments\Model\ResourceModel\Icon\Collection $collection */
        $collection = $this->iconCollectionFactory->create();

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
        $icons = [];

        foreach ($collection as $icon) {
            $icons[] = $this->get($icon->getId());
        }
        $searchResults->setItems($icons);
        return $searchResults;
    }

    /**
     * Delete File
     *
     * @param IconInterface $icon
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(IconInterface $icon)
    {
        try {
            $this->resource->delete($icon);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete icon by given icon Identity
     *
     * @param string $iconId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($iconId)
    {
        return $this->delete($this->getById($iconId));
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
        \Magezon\ProductAttachments\Model\ResourceModel\Icon\Collection $collection
    ) {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), $filter->getValue());
        }
    }

    /**
     * @param $iconId
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function get($iconId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$iconId][$cacheKey])) {
            /** @var Icon $icon */
            $icon = $this->iconFactory->create();
            $icon->load($iconId);

            if (!$icon->getId()) {
                throw NoSuchEntityException::singleField('id', $iconId);
            }
            $this->instances[$iconId][$cacheKey] = $icon;
        }
        return $this->instances[$iconId][$cacheKey];
    }
}
