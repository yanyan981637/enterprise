<?php
namespace Mitac\Theme\Model;
use Mitac\Theme\Api\Data\ZohoAccessTokenInterface;
use Magento\Framework\Model\AbstractModel;

class ZohoAccessToken extends AbstractModel implements ZohoAccessTokenInterface
{
    protected function _construct()
    {
        $this->_init(\Mitac\Theme\Model\ResourceModel\ZohoAccessToken::class);
    }
    public function getId()
    {
        return $this->getData('id');
    }

    public function setId($id)
    {
        $this->setData('id', $id);
    }

    public function getAccessToken()
    {
        return $this->getData('access_token');
    }

    public function setAccessToken($accessToken){
        $this->setData('access_token', $accessToken);
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    public function setStoreId($storeId){
        $this->setData('store_id', $storeId);
    }

}
