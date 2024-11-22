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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Api\Data\TagInterface;
use Magezon\Blog\Api\Data\TagSearchResultsInterface;
use Magezon\Blog\Api\Data\TagSearchResultsInterfaceFactory;
use Magezon\Blog\Api\TagRepositoryInterface;
use Magezon\Blog\Model\ResourceModel\Tag\Collection;
use Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magento\Framework\Api\SortOrder;

class TagRepository implements TagRepositoryInterface
{
    /**
     * @var Tag[]
     */
    protected $instances = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magezon\Blog\Model\TagFactory
     */
    protected $tagFactory;

    /**
     * @var CollectionFactory
     */
    protected $tagCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Tag
     */
    protected $tagResource;

    /**
     * @var TagSearchResultsInterfaceFactory
     */
    protected $tagSearchResultsFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param TagFactory $tagFactory
     * @param CollectionFactory $tagCollectionFactory
     * @param ResourceModel\Tag $tagResource
     * @param TagSearchResultsInterfaceFactory $tagSearchResultsFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TagFactory $tagFactory,
        CollectionFactory $tagCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Tag $tagResource,
        TagSearchResultsInterfaceFactory $tagSearchResultsFactory
    ) {
        $this->storeManager            = $storeManager;
        $this->tagFactory              = $tagFactory;
        $this->tagCollectionFactory    = $tagCollectionFactory;
        $this->tagResource             = $tagResource;
        $this->tagSearchResultsFactory = $tagSearchResultsFactory;
    }

    /**
     * @param TagInterface $tag
     * @return TagInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function save(TagInterface $tag)
    {
        $storeId = $tag->getStoreId();
        if (!$storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        if ($tag->getId()) {
            $newData    = $tag->getData();
            $tag = $this->get($tag->getId(), $storeId);
            foreach ($newData as $k => $v) {
                $tag->setData($k, $v);
            }
        }

        try {
            $this->tagResource->save($tag);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save tag: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        unset($this->instances[$tag->getId()]);
        return $this->get($tag->getId(), $storeId);
    }

    /**
     * Retrieve tag.
     *
     * @param int $tagId
     * @param int $storeId
     * @return TagInterface
     * @throws LocalizedException
     */
    public function get($tagId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$tagId][$cacheKey])) {
            /** @var Tag $tag */
            $tag = $this->tagFactory->create();
            if (null !== $storeId) {
                $tag->setStoreId($storeId);
            }
            $tag->load($tagId);

            if (!$tag->getId()) {
                throw NoSuchEntityException::singleField('id', $tagId);
            }
            $this->instances[$tagId][$cacheKey] = $tag;
        }
        return $this->instances[$tagId][$cacheKey];
    }

    /**
     * @param TagInterface $tag
     * @return true
     * @throws StateException
     */
    public function delete(TagInterface $tag)
    {
        try {
            $tagId = $tag->getId();
            $this->tagResource->delete($tag);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete tag with id %1',
                    $tag->getId()
                ),
                $e
            );
        }
        unset($this->instances[$tagId]);
        return true;
    }

    /**
     * @param $tagId
     * @return true
     * @throws LocalizedException
     * @throws StateException
     */
    public function deleteById($tagId)
    {
        $tag = $this->get($tagId);
        return  $this->delete($tag);
    }

    /**
     * Load tag data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TagSearchResultsInterface
     * @throws LocalizedException
     * @throws InputException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->tagSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->tagCollectionFactory->create();

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
        $tags = [];

        foreach ($collection as $tag) {
            $tags[] = $this->get($tag->getId());
        }
        $searchResults->setItems($tags);
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
