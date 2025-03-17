<?php

namespace Mitac\Theme\Model;
use Magento\Framework\Api\SearchCriteriaInterface;
use Mitac\Theme\Api\Data\ZohoAccessTokenInterface;
use Mitac\Theme\Api\ZohoAccessTokenRepositoryInterface;
use Mitac\Theme\Model\ResourceModel\ZohoAccessToken as ResourceZohoAccessToken;
use Magento\Framework\Exception\NoSuchEntityException;
class ZohoAccessTokenRepository implements ZohoAccessTokenRepositoryInterface
{
    private $resource;
    private $zohoAccessTokenFactory;
    private $zohoAccessTokenCollectionFactory;

    private $storeManager;

    public function __construct(
        ResourceZohoAccessToken $resource,
        ZohoAccessTokenFactory $zohoAccessTokenFactory,
        ResourceModel\ZohoAccessToken\CollectionFactory $zohoAccessTokenCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->zohoAccessTokenFactory = $zohoAccessTokenFactory;
        $this->zohoAccessTokenCollectionFactory = $zohoAccessTokenCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function getById($id)
    {
        $zohoAccessToken = $this->zohoAccessTokenFactory->create();
        $this->resource->load($zohoAccessToken, $id);
        if (!$zohoAccessToken->getId()) {
            throw new NoSuchEntityException(__('Color with id "%1" does not exist.', $id));
        }
        return $zohoAccessToken;
    }

    public function save(ZohoAccessTokenInterface $zohoAccessToken)
    {
        $this->resource->save($zohoAccessToken);
        return $zohoAccessToken;
    }

    public function delete(ZohoAccessTokenInterface $zohoAccessToken)
    {
        $this->resource->delete($zohoAccessToken);
        return true;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->zohoAccessTokenCollectionFactory->create();
        return $collection;
    }

    public function getExitAccessToken($storeId = null)
    {
        if(!$storeId){
            $storeId = $this->storeManager->getStore()->getId();
        }

        $collection = $this->zohoAccessTokenCollectionFactory->create();
        $collection->addFieldToFilter('store_id', $storeId)->setOrder('id', 'desc');
        if($collection->getSize()){
            return $collection->getFirstItem()->getAccessToken();
        }else {
            return null;
        }

    }

    public function setNewAccessToken($accessToken, $storeId = null)
    {
        if(!$storeId){
            $storeId = $this->storeManager->getStore()->getId();
        }

        $data = $this->zohoAccessTokenFactory->create();
        $data->setAccessToken($accessToken);
        $data->setStoreId((int)$storeId);
        $this->save($data);

    }

}
