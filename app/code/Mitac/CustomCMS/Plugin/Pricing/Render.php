<?php
namespace Mitac\CustomCMS\Plugin\Pricing;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\SaleableInterface;
class Render {
    protected $scopeConfig;
    private $isCartEnable = true;
    public function __construct(ScopeConfigInterface $scopeConfig) {
        $this->scopeConfig = $scopeConfig;
        $this->isCartEnable = $this->scopeConfig->getValue('cart_setting/setting/enablecart', ScopeInterface::SCOPE_STORE);
    }

    public function aroundRender(\Magento\Framework\Pricing\Render $subject, $proceed, $priceCode, SaleableInterface $saleableItem, array $arguments = []) {
      if(!$this->isCartEnable){
        return '';
      }
      if(!$saleableItem->getData('display_price')){
        return '';
      }
      return $proceed($priceCode, $saleableItem, $arguments);

    }
}