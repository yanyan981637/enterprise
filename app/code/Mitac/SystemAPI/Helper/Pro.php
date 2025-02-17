<?php
namespace Mitac\SystemAPI\Helper;

class Pro extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * @var ResourceConnection
     */
	protected $resource;

	/**
     * @var StoreManagerInterface
     */
	protected $storeManager;

	/**
     * @var ScopeConfigInterface
     */
	protected $scopeConfig;

	public function __construct(
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{
		$this->resource = $resource;
		$this->storeManager = $storeManager;
		$this->scopeConfig = $scopeConfig;
	}

  public function getCustomerDevice($CustomId)
  {
    //--------------------------------------------------------------------//
    //抓取該客戶註冊過產品
    #region
      $CustomerDevice = Array();

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

      $StoreId = $this->storeManager->getStore()->getStoreId();
      
      if(!empty($StoreId) && !empty($CustomId))
      {
        $Devices = $this->getProductRegister($CustomId);
              
        if(!empty($Devices))
        {
          $CustomerDevice = Array(
                              'status' => "200",
                              'message' => "Data is find"
                            );

          foreach ($Devices as $key => $value)
          {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->setStoreId($StoreId)->load($value['device_id']);
            $WebsiteIds = $product->getWebsiteIds();

            if(!empty($WebsiteIds))
            {
              if(in_array($StoreId,$WebsiteIds))
              {
                $BaseUrl         = $this->scopeConfig->getValue('web/unsecure/base_url');
                //----------------------------------------------------------------------------------//
                $imgproduct = $this->productRepositoryInterface->getById(trim($value['device_id']),false,$StoreId);
                $productImageUrl = $BaseUrl.'media/catalog/product'.$imgproduct->getImage();
                    
                if(!empty($productImageUrl) && strpos($productImageUrl,'productno_selection')==false)
                {
                  $productImageUrl = str_replace($BaseUrl, '', $productImageUrl);
                  $productImageUrl = str_replace('pub/media/catalog/product', '', $productImageUrl);
                  $productImageUrl = str_replace('media/catalog/product', '', $productImageUrl);
                  $img = $productImageUrl;

                  $value['img'] = $img;
                }
                else
                {
                  $img = '';
                  $value['img'] = $img;
                }
                //----------------------------------------------------------------------------------//
                unset($value['id']);
                $CustomerDevice['device'][] = $value;
              }
            }
          }

          if(empty($CustomerDevice))
          {
            $CustomerDevice = Array(
                                'status' => "505",
                                'message' => "Data Error"
                              );
          }
        }
        else
        {
          $CustomerDevice = Array(
                              'status' => "200",
                              'message' => "Data is find"
                            );
        }
      }
      else
      {
        $CustomerDevice = Array(
                            'status' => "505",
                            'message' => "Data Error"
                          );
      }
    #end
    //--------------------------------------------------------------------//
    return Array($CustomerDevice);
  }

}
