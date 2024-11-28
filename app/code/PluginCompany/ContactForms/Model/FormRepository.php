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

use Magento\Framework\Api\SortOrder;
use PluginCompany\ContactForms\Api\FormRepositoryInterface;
use PluginCompany\ContactForms\Model\ResourceModel\Form\CollectionFactory as FormCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PluginCompany\ContactForms\Api\Data\FormSearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use PluginCompany\ContactForms\Api\Data\FormInterfaceFactory;
use PluginCompany\ContactForms\Model\ResourceModel\Form as ResourceForm;

class FormRepository implements FormRepositoryInterface
{

    protected $dataObjectProcessor;

    protected $formFactory;

    protected $dataFormFactory;

    protected $searchResultsFactory;

    private $storeManager;

    protected $dataObjectHelper;

    protected $resource;

    protected $formCollectionFactory;


    /**
     * @param ResourceForm $resource
     * @param FormFactory $formFactory
     * @param FormInterfaceFactory $dataFormFactory
     * @param FormCollectionFactory $formCollectionFactory
     * @param FormSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceForm $resource,
        FormFactory $formFactory,
        FormInterfaceFactory $dataFormFactory,
        FormCollectionFactory $formCollectionFactory,
        FormSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->formFactory = $formFactory;
        $this->formCollectionFactory = $formCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFormFactory = $dataFormFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \PluginCompany\ContactForms\Api\Data\FormInterface $form
    ) {
        /* if (empty($form->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $form->setStoreId($storeId);
        } */
        try {
            $form->getResource()->save($form);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the form: %1',
                $exception->getMessage()
            ));
        }
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($formId)
    {
        $form = $this->formFactory->create();
        $form->getResource()->load($form, $formId);
        if (!$form->getId()) {
            throw new NoSuchEntityException(__('Form with id "%1" does not exist.', $formId));
        }
        return $form;
    }

    /**
     * @param $formId
     * @return Form
     */
    public function getByIdOrNew($formId)
    {
        $form = $this->formFactory->create();
        $form->getResource()->load($form, $formId);
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->formCollectionFactory->create();
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
        \PluginCompany\ContactForms\Api\Data\FormInterface $form
    ) {
        try {
            $form->getResource()->delete($form);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Form: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($formId)
    {
        return $this->delete($this->getById($formId));
    }

    public function getByUrlKey($urlKey, $storeId, $active = true)
    {
        $form = $this->formFactory->create();
        $id = $form
            ->getResource()
            ->checkUrlKey($urlKey, $storeId, $active)
            ;
        return $this->getById($id);
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        /** @var \PluginCompany\ContactForms\Model\ResourceModel\Form\Collection $collection */
        $collection = $this->formCollectionFactory->create();
        return $collection->getAllIds();
    }

}
