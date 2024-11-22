<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model;

use Magefan\TranslationPlus\Api\Data\TranslationIndexInterfaceFactory;
use Magefan\TranslationPlus\Api\Data\TranslationIndexSearchResultsInterfaceFactory;
use Magefan\TranslationPlus\Api\TranslationIndexRepositoryInterface;
use Magefan\TranslationPlus\Model\ResourceModel\TranslationIndex as ResourceTranslationIndex;
use Magefan\TranslationPlus\Model\ResourceModel\TranslationIndex\CollectionFactory as TranslationIndexCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class TranslationIndexRepositoryTranslation implements TranslationIndexRepositoryInterface
{
    /**
     * @var ResourceTranslationIndex
     */
    protected $resource;

    /**
     * Index Table Factory class
     */
    protected $TranslationIndexFactory;

    /**
     * @var TranslationIndexCollectionFactory
     */
    protected $TranslationIndexCollectionFactory;

    /**
     * @var TranslationIndexSearchResultsInterfaceFactory
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
     * @var TranslationIndexInterfaceFactory
     */
    protected $dataTranslationIndexFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    public function __construct(
        ResourceTranslationIndex $resource,
        TranslationIndexInterfaceFactory $dataTranslationIndexFactory,
        TranslationIndexCollectionFactory $TranslationIndexCollectionFactory,
        TranslationIndexSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->TranslationIndexCollectionFactory = $TranslationIndexCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTranslationIndexFactory = $dataTranslationIndexFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param  \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface $TranslationIndex
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface
     * @throws CouldNotSaveException
     */
    public function save(
        \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface $TranslationIndex
    ) {
        $TranslationIndexData = $this->extensibleDataObjectConverter->toNestedArray(
            $TranslationIndex,
            [],
            \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface::class
        );

        $TranslationIndexModel = $this->TranslationIndexFactory->create()->setData($TranslationIndexData);

        try {
            $this->resource->save($TranslationIndexModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the TranslationIndex: %1',
                    $exception->getMessage()
                )
            );
        }
        return $TranslationIndexModel->getDataModel();
    }

    /**
     * @param  string $TranslationIndexId
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface
     * @throws NoSuchEntityException
     */
    public function get($TranslationIndexId)
    {
        $TranslationIndex = $this->TranslationIndexFactory->create();
        $this->resource->load($TranslationIndex, $TranslationIndexId);
        if (!$TranslationIndex->getId()) {
            throw new NoSuchEntityException(__('TranslationIndex with id "%1" does not exist.', $TranslationIndexId));
        }
        return $TranslationIndex->getDataModel();
    }

    /**
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->TranslationIndexCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param  \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface $TranslationIndex
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(
        \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface $TranslationIndex
    ) {
        try {
            $TranslationIndexModel = $this->TranslationIndexFactory->create();
            $this->resource->load($TranslationIndexModel, $TranslationIndex->getTranslationIndexId());
            $this->resource->delete($TranslationIndexModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the TranslationIndex: %1',
                    $exception->getMessage()
                )
            );
        }
        return true;
    }

    /**
     * @param  string $TranslationIndexId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($TranslationIndexId)
    {
        return $this->delete($this->get($TranslationIndexId));
    }
}
