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
use Magezon\Blog\Api\CommentRepositoryInterface;
use Magezon\Blog\Api\Data\CommentInterface;
use Magezon\Blog\Api\Data\CommentSearchResultsInterface;
use Magezon\Blog\Api\Data\CommentSearchResultsInterfaceFactory;
use Magezon\Blog\Model\ResourceModel\Comment\Collection;
use Magezon\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Framework\Api\SortOrder;

class CommentRepository implements CommentRepositoryInterface
{
    /**
     * @var Comment[]
     */
    protected $instances = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magezon\Blog\Model\CommentFactory
     */
    protected $commentFactory;

    /**
     * @var CollectionFactory
     */
    protected $commentCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Comment
     */
    protected $commentResource;

    /**
     * @var CommentSearchResultsInterfaceFactory
     */
    protected $commentSearchResultsFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CommentFactory $commentFactory
     * @param CollectionFactory $commentCollectionFactory
     * @param ResourceModel\Comment $commentResource
     * @param CommentSearchResultsInterfaceFactory $commentSearchResultsFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CommentFactory $commentFactory,
        CollectionFactory $commentCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Comment $commentResource,
        CommentSearchResultsInterfaceFactory $commentSearchResultsFactory
    ) {
        $this->storeManager                = $storeManager;
        $this->commentFactory              = $commentFactory;
        $this->commentCollectionFactory    = $commentCollectionFactory;
        $this->commentResource             = $commentResource;
        $this->commentSearchResultsFactory = $commentSearchResultsFactory;
    }

    /**
     * @param CommentInterface $comment
     * @return CommentInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function save(CommentInterface $comment)
    {
        $storeId = $comment->getStoreId();
        if (!$storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        if ($comment->getId()) {
            $newData    = $comment->getData();
            $comment = $this->get($comment->getId(), $storeId);
            foreach ($newData as $k => $v) {
                $comment->setData($k, $v);
            }
        }

        try {
            $this->commentResource->save($comment);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save comment: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        unset($this->instances[$comment->getId()]);
        return $this->get($comment->getId(), $storeId);
    }

    /**
     * Retrieve comment.
     *
     * @param int $commentId
     * @param int $storeId
     * @return CommentInterface
     * @throws LocalizedException
     */
    public function get($commentId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$commentId][$cacheKey])) {
            /** @var Comment $comment */
            $comment = $this->commentFactory->create();
            if (null !== $storeId) {
                $comment->setStoreId($storeId);
            }
            $comment->load($commentId);

            if (!$comment->getId()) {
                throw NoSuchEntityException::singleField('id', $commentId);
            }
            $this->instances[$commentId][$cacheKey] = $comment;
        }
        return $this->instances[$commentId][$cacheKey];
    }

    /**
     * Delete comment.
     *
     * @param CommentInterface $comment
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(CommentInterface $comment)
    {
        try {
            $commentId = $comment->getId();
            $this->commentResource->delete($comment);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete comment with id %1',
                    $comment->getId()
                ),
                $e
            );
        }
        unset($this->instances[$commentId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($commentId)
    {
        $comment = $this->get($commentId);
        return  $this->delete($comment);
    }

    /**
     * Load comment data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CommentSearchResultsInterface
     * @throws LocalizedException
     * @throws InputException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->commentSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->commentCollectionFactory->create();

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
        $comments = [];

        foreach ($collection as $comment) {
            $comments[] = $this->get($comment->getId());
        }
        $searchResults->setItems($comments);
        return $searchResults;
    }

    /**
     *
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
