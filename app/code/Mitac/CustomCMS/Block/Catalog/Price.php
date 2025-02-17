<?php
namespace Mitac\CustomCMS\Block\Catalog;

use Magento\Framework\Locale\ResolverInterface;
use Mitac\SystemAPI\Helper\Data;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;

class Price extends \Magento\Framework\View\Element\Template
{
    
	protected $storeManager;


    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;


    protected $price_tool;
    
    protected $helper;

    protected $customer_session;

    protected $systemApi;

    private $tier_price_api;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        \Magento\Catalog\Helper\Output $helper,
        \Magento\Customer\Model\Session $customer_session,
        Data $systemApi,
        ScopedProductTierPriceManagementInterface $tier_price_api,
		array $data = []
	) 
	{
		$this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->helper = $helper;
        $this->customer_session = $customer_session;
        $this->systemApi = $systemApi;
        $this->tier_price_api = $tier_price_api;
		parent::__construct($context, $data);
	}

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->_localeResolver->getLocale();
    }
    public function getCustomPrice($product, $prefix='', $suffix=''){
        // $prefix='';$suffix='';

        $storeId = $this->getStoreId();

        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();


        $product_type = $product->getTypeId();
        // $finalPrice   = $product->getPriceInfo()->getPrice('final_price')->getValue();
        // echo $finalPrice;
        // echo $product_type;
        $minimumPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
        // echo $minimumPrice;
        switch ($product_type) {
            case 'bundle':
                $minimumPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
                $maximumPrice = $product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue();
                
                return '<span class="price-range">'. $prefix . $this->formatPrice($minimumPrice). $suffix . '+</span>';
            default:
                //  原價
                $price =  $this->helper->productAttribute($product, $product->getPrice(), 'price');
                // echo 'price'.$price;
                // 優惠價
                $currdate = $this->systemApi->getCurrentLocaleDateTime(null, "Y-m-d");
                $special_price = 0;
                $check_special_price =  $product->getResource()->getAttribute('special_price')->getFrontend()->getValue($product);
                if(!empty($check_special_price))
                {
                    $check_special_From =  $product->getResource()->getAttribute('special_from_date')->getFrontend()->getValue($product);
                    $check_special_To =  $product->getResource()->getAttribute('special_to_date')->getFrontend()->getValue($product);

                    if(!empty($check_special_From) && !empty($check_special_To))
                    {
                        if(strtotime($check_special_From)<=strtotime($currdate) && strtotime($currdate)<=strtotime($check_special_To))
                        {
                            $special_price =  $check_special_price;
                        }
                    }
                    else if(empty($check_special_From) && !empty($check_special_To))
                    {
                        if(strtotime($currdate)<=strtotime($check_special_To))
                        {
                            $special_price =  $check_special_price;
                        }
                    }
                    else if(!empty($check_special_From) && empty($check_special_To))
                    {
                        if(strtotime($check_special_From)<=strtotime($currdate))
                        {
                            $special_price =  $check_special_price;
                        }
                    }
                }

                // 群組價


                $group_price = $this->getTirdPrice($product);


                if ($group_price === 0 and $special_price === 0) {
                    return '<span class="price"><span class="price">' . $prefix .
                    $this->formatPrice($price)
                        .$suffix .'</span></span>';
                } elseif ($group_price === 0 and $special_price !== 0){
                    return '<span class="old-price"><span class="price">'. $prefix .
                            $this->formatPrice($price).$suffix .'</span></span>
                        <span class="special-price">'. $prefix .
                            $this->formatPrice($special_price)
                        .$suffix .'</span>';
                } elseif($group_price !== 0 and $special_price === 0){
                    return '<span class="old-price"><span class="price">' . $prefix .
                            $this->formatPrice($price).$suffix .'</span></span>
                        <span class="group-price">'. $prefix .
                            $this->formatPrice($group_price)
                        .$suffix .'</span>';
                } else {
                    if ($special_price <=  $group_price){
                        return '<span class="old-price"><span class="price">'. $prefix .
                            $this->formatPrice($price).$suffix .'</span></span>
                        <span class="special-price">'. $prefix .
                            $this->formatPrice($special_price)
                        .$suffix .'</span>';
                    } else {
                        return '<span class="old-price"><span class="price">'. $prefix .
                            $this->formatPrice($price).$suffix .'</span></span>
                        <span class="group-price">'. $prefix .
                            $this->formatPrice($group_price)
                        .$suffix .'</span>';
                    }
                }
        }

    }

    /**
     * @var product
     * @description : 得到群組價格： 
     *                  1. 用戶未登入時， 群組為 Not logged in (0), 
     *                  2. 用戶登入後，群組為 $this->customer_session->getCustomer()->getGroupId()
     *                  3. 所有群組包含 Not logged in 及 後台設定的所有群組
     *                  4. 如果當前群組id 有設定 所有群組tird price， 則取該值，
     *                  5.  當前群組id 沒有設定 所有群組tird price， 則取所有群組值
     *                  6. 當前群組的 所有群組tird price 優先 所有群組tird， 二者之間不做比較
     * @return       { float } price
     */    

    private function getTirdPrice($product){
        
        $customerGroupId = $this->customer_session->isLoggedIn() ? $this->customer_session->getCustomer()->getGroupId() : 0;
        
        // 得到當前群組價
        $current_group_price = $this->tier_price_api->getList($product->getSku(), $customerGroupId);

        $all_tier_price = [];
        // echo 'customer group id: ' . $customerGroupId;
        if(count($current_group_price) > 0) {

            foreach ($current_group_price as $group_price) {
                $all_tier_price[] = $group_price->getValue();
            }

            return min($all_tier_price);

        }

        $all_group_price = $this->tier_price_api->getList($product->getSku(), 'all');

        if(count($all_group_price) > 0) {

            foreach ($all_group_price as $group_price) {
                $all_tier_price[] = $group_price->getValue();
            }

            return min($all_tier_price);

        }

        return 0;

    }

    private function formatPrice($price) {
        $currencySymbol = $this->_storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
        return $currencySymbol.number_format($price, 2, '.', ',');;
    }

}