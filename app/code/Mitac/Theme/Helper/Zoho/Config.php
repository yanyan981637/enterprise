<?php

namespace Mitac\Theme\Helper\Zoho;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mitac\Theme\Api\ZohoAccessTokenRepositoryInterface;
class Config
{

    /**
     * @var ScopeConfigInterface $scopeConfig
     * */
    protected $scopeConfig;

    /**
     * @var ZohoAccessTokenRepositoryInterface $zohoAccessTokenRepository
     */
    protected $zohoAccessTokenRepository;
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ZohoAccessTokenRepositoryInterface $zohoAccessTokenRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->zohoAccessTokenRepository = $zohoAccessTokenRepository;
    }

    public function getEnable(){
        return $this->getStoreConfig('subscribe/general/enable');
    }

    public function getApiAuthPath(){
        return $this->getStoreConfig('subscribe/general/api_auth_path');
    }
    public function getApiSubscribePath(){
        return $this->getStoreConfig('subscribe/general/api_subscribe_path');
    }
    public function getApiUnsubscribePath(){
        return $this->getStoreConfig('subscribe/general/api_unsubscribe_path');
    }
    public function getClientId(){
        return $this->getStoreConfig('subscribe/general/client_id');
    }
    public function getClientSecret(){
        return $this->getStoreConfig('subscribe/general/client_secret');
    }
    public function getRedirectUri(){
        return $this->getStoreConfig('subscribe/general/redirect_uri');
    }
    public function getlistkey(){
        return $this->getStoreConfig('subscribe/general/listkey');
    }

    public function getRefreshToken()
    {
        return $this->getStoreConfig('subscribe/general/refresh_token');
    }

    public function getAccessToken()
    {
        return $this->zohoAccessTokenRepository->getExitAccessToken();
    }
    public function setAccessToken($access_token)
    {
        $this->zohoAccessTokenRepository->setNewAccessToken($access_token);
    }

    private function getStoreConfig(string $path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

}
