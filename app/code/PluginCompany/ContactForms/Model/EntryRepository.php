<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 * 
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 * 
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 * 
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 * 
 * SUPPORT@PLUGIN.COMPANY
 */

namespace PluginCompany\ContactForms\Model;

use PluginCompany\ContactForms\Api\Data\EntryInterfaceFactory;
use PluginCompany\ContactForms\Api\Data\EntrySearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use PluginCompany\ContactForms\Api\EntryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PluginCompany\ContactForms\Model\ResourceModel\Entry as ResourceEntry;
use Magento\Framework\Exception\CouldNotDeleteException;
use PluginCompany\ContactForms\Model\ResourceModel\Entry\CollectionFactory as EntryCollectionFactory;

class EntryRepository implements EntryRepositoryInterface
{

    protected $entryCollectionFactory;

    protected $dataObjectProcessor;

    protected $searchResultsFactory;

    private $storeManager;

    protected $dataObjectHelper;

    protected $dataEntryFactory;

    protected $resource;

    protected $entryFactory;


    /**
     * @param ResourceEntry $resource
     * @param EntryFactory $entryFactory
     * @param EntryInterfaceFactory $dataEntryFactory
     * @param EntryCollectionFactory $entryCollectionFactory
     * @param EntrySearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceEntry $resource,
        EntryFactory $entryFactory,
        EntryInterfaceFactory $dataEntryFactory,
        EntryCollectionFactory $entryCollectionFactory,
        EntrySearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->entryFactory = $entryFactory;
        $this->entryCollectionFactory = $entryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataEntryFactory = $dataEntryFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \PluginCompany\ContactForms\Api\Data\EntryInterface $entry
    ) {
        /* if (empty($entry->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $entry->setStoreId($storeId);
        } */
        try {
            $entry->getResource()->save($entry);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the entry: %1',
                $exception->getMessage()
            ));
        }
        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entryId)
    {
        $entry = $this->entryFactory->create();
        $entry->getResource()->load($entry, $entryId);
        if (!$entry->getId()) {
            throw new NoSuchEntityException(__('Entry with id "%1" does not exist.', $entryId));
        }
        return $entry;
    }

    public function getByIdOrNew($entryId)
    {
        $entry = $this->entryFactory->create();
        $entry->getResource()->load($entry, $entryId);
        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->entryCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \PluginCompany\ContactForms\Api\Data\EntryInterface $entry
    ) {
        try {
            $entry->getResource()->delete($entry);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Entry: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($entryId)
    {
        return $this->delete($this->getById($entryId));
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        /** @var \PluginCompany\ContactForms\Model\ResourceModel\Entry\Collection $collection */
        $collection = $this->entryCollectionFactory->create();
        return $collection->getAllIds();
    }

    public function getAllIdsByFormId($formId)
    {
        /** @var \PluginCompany\ContactForms\Model\ResourceModel\Entry\Collection $collection */
        $collection = $this->entryCollectionFactory->create();
        $collection->addFieldToFilter('form_id', $formId);
        return $collection->getAllIds();
    }

    /**
     * @return array
     */
    public function getLastId()
    {
        /** @var \PluginCompany\ContactForms\Model\ResourceModel\Entry\Collection $collection */
        $collection = $this->entryCollectionFactory->create();
        $lastItem = $collection->addOrder('entity_id')->getFirstItem();
        if(!$lastItem || !$lastItem->getId()) {
            return 0;
        }
        return $lastItem->getId();
    }

}
