<?php
namespace Mitac\CustomCMS\Block\Checkout;

use Magento\Store\Model\StoreManagerInterface;

class ShippingAddress extends \Magento\Framework\View\Element\Template
{

    protected $scopeConfig;

    protected $countryFactory;

    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = []
    )
    {
        $this->_countryFactory = $countryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getCountryname($countryCode){    
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    public function getShippingCountries(){
        $ShippingCountries =  $this->scopeConfig->getValue('general/country/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
        return $ShippingCountries;
    }

    public function HasShippingCountries()
    {
        return !empty($this->getShippingCountries());
    }

    public function formatShippingCountries()
    {
        $Countries = array();
        $getShippingCountries = explode(',',  $this->getShippingCountries());
        if($this->HasShippingCountries()){
            foreach($getShippingCountries as $Country ){
                $Countries[] = $this->getCountryname($Country);
            }
        }
        return $Countries;
    }
    
}
?>