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
use Magezon\Blog\Api\Data\PostInterface;
use Magezon\Blog\Api\Data\PostSearchResultsInterface;
use Magezon\Blog\Api\Data\PostSearchResultsInterfaceFactory;
use Magezon\Blog\Api\PostRepositoryInterface;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\Api\SortOrder;

class PostRepository implements PostRepositoryInterface
{
    /**
     * @var Post[]
     */
    protected $instances = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Post
     */
    protected $postResource;

    /**
     * @var PostSearchResultsInterfaceFactory
     */
    protected $postSearchResultsFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param PostFactory $postFactory
     * @param CollectionFactory $postCollectionFactory
     * @param ResourceModel\Post $postResource
     * @param PostSearchResultsInterfaceFactory $postSearchResultsFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PostFactory $postFactory,
        CollectionFactory $postCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Post $postResource,
        PostSearchResultsInterfaceFactory $postSearchResultsFactory
    ) {
        $this->storeManager             = $storeManager;
        $this->postFactory              = $postFactory;
        $this->postCollectionFactory    = $postCollectionFactory;
        $this->postResource             = $postResource;
        $this->postSearchResultsFactory = $postSearchResultsFactory;
    }

    /**
     * Save post.
     *
     * @param PostInterface $post
     * @return PostInterface
     * @throws LocalizedException
     */
    public function save(PostInterface $post)
    {
        $storeId = $post->getStoreId();
        if (!$storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        if ($post->getId()) {
            $newData    = $post->getData();
            $post = $this->get($post->getId(), $storeId);
            foreach ($newData as $k => $v) {
                $post->setData($k, $v);
            }
        }

        try {
            $this->postResource->save($post);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save post: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        unset($this->instances[$post->getId()]);
        return $this->get($post->getId(), $storeId);
    }

    /**
     * Retrieve post.
     *
     * @param int $postId
     * @param int $storeId
     * @return PostInterface
     * @throws LocalizedException
     */
    public function get($postId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$postId][$cacheKey])) {
            /** @var Post $post */
            $post = $this->postFactory->create();
            if (null !== $storeId) {
                $post->setStoreId($storeId);
            }
            $post->load($postId);

            if (!$post->getId()) {
                throw NoSuchEntityException::singleField('id', $postId);
            }
            $this->instances[$postId][$cacheKey] = $post;
        }
        return $this->instances[$postId][$cacheKey];
    }

    /**
     * Delete post.
     *
     * @param PostInterface $post
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(PostInterface $post)
    {
        try {
            $postId = $post->getId();
            $this->postResource->delete($post);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete post with id %1',
                    $post->getId()
                ),
                $e
            );
        }
        unset($this->instances[$postId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($postId)
    {
        $post = $this->get($postId);
        return  $this->delete($post);
    }

    /**
     * Load post data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return PostSearchResultsInterface
     * @throws LocalizedException
     * @throws InputException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->postSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->postCollectionFactory->create();

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
        $posts = [];

        foreach ($collection as $post) {
            $posts[] = $this->get($post->getId());
        }
        $searchResults->setItems($posts);
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws InputException
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
