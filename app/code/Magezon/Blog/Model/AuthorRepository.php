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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Api\AuthorRepositoryInterface;
use Magezon\Blog\Api\Data\AuthorInterface;
use Magezon\Blog\Api\Data\AuthorSearchResultsInterface;
use Magezon\Blog\Api\Data\AuthorSearchResultsInterfaceFactory;
use Magezon\Blog\Model\ResourceModel\Author\Collection;
use Magezon\Blog\Model\ResourceModel\Author\CollectionFactory;

class AuthorRepository implements AuthorRepositoryInterface
{
    /**
     * @var Author[]
     */
    protected $instances = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AuthorFactory
     */
    protected $authorFactory;

    /**
     * @var CollectionFactory
     */
    protected $authorCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Author
     */
    protected $authorResource;

    /**
     * @var AuthorSearchResultsInterfaceFactory
     */
    protected $authorSearchResultsFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param AuthorFactory $authorFactory
     * @param CollectionFactory $authorCollectionFactory
     * @param ResourceModel\Author $authorResource
     * @param AuthorSearchResultsInterfaceFactory $authorSearchResultsFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AuthorFactory $authorFactory,
        CollectionFactory $authorCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Author $authorResource,
        AuthorSearchResultsInterfaceFactory $authorSearchResultsFactory
    ) {
        $this->storeManager               = $storeManager;
        $this->authorFactory              = $authorFactory;
        $this->authorCollectionFactory    = $authorCollectionFactory;
        $this->authorResource             = $authorResource;
        $this->authorSearchResultsFactory = $authorSearchResultsFactory;
    }

    /**
     * @param AuthorInterface $author
     * @return AuthorInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function save(AuthorInterface $author)
    {
        $storeId = $author->getStoreId();
        if (!$storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        if ($author->getId()) {
            $newData    = $author->getData();
            $author = $this->get($author->getId(), $storeId);
            foreach ($newData as $k => $v) {
                $author->setData($k, $v);
            }
        }

        try {
            $this->authorResource->save($author);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save author: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        unset($this->instances[$author->getId()]);
        return $this->get($author->getId(), $storeId);
    }

    /**
     * Retrieve author.
     *
     * @param int $authorId
     * @param int $storeId
     * @return AuthorInterface
     * @throws LocalizedException
     */
    public function get($authorId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$authorId][$cacheKey])) {
            /** @var Author $author */
            $author = $this->authorFactory->create();
            if (null !== $storeId) {
                $author->setStoreId($storeId);
            }
            $author->load($authorId);

            if (!$author->getId()) {
                throw NoSuchEntityException::singleField('id', $authorId);
            }
            $this->instances[$authorId][$cacheKey] = $author;
        }
        return $this->instances[$authorId][$cacheKey];
    }

    /**
     * @param AuthorInterface $author
     * @return true
     * @throws StateException
     */
    public function delete(AuthorInterface $author)
    {
        try {
            $authorId = $author->getId();
            $this->authorResource->delete($author);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete author with id %1',
                    $author->getId()
                ),
                $e
            );
        }
        unset($this->instances[$authorId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($authorId)
    {
        $author = $this->get($authorId);
        return  $this->delete($author);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return AuthorSearchResultsInterface
     * @throws LocalizedException
     * @throws InputException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->authorSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->authorCollectionFactory->create();

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
        $authors = [];

        foreach ($collection as $author) {
            $authors[] = $this->get($author->getId());
        }
        $searchResults->setItems($authors);
        return $searchResults;
    }

    /**
     * @param FilterGroup $filterGroup
     * @param ResourceModel\Author\Collection $collection
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
