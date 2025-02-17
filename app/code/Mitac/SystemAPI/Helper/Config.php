<?php
namespace Mitac\SystemAPI\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
class Config extends \Magento\Framework\App\Helper\AbstractHelper {

  private $scoprConfig;

  protected $scopeStore;

  private $categoryRepository;

  public function __construct(
      ScopeConfigInterface $scoprConfig
  ){
      $this->scoprConfig = $scoprConfig;
      $this->scopeStore = ScopeInterface::SCOPE_STORE;
  }

  public function getIsCartEnabled($store_id = null) {
    return $this->scoprConfig->getValue('cart_setting/setting/enablecart', $this->scopeStore, $store_id);
}

}