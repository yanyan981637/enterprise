<?php
namespace Mitac\SystemAPI\Helper;

class Key extends \Magento\Framework\App\Helper\AbstractHelper
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

	public function getSNByProductid($productid, $serialnumber)
	{
		$sqlString = "select mds.id, mds.model_id, mds.product_id, mds.product_name, mds.customer_id, mds.used, mds.launch_year
					  from mitac_device_serials mds
					  where mds.product_id = '".$productid."' 
					  	and mds.serial_number = '".$serialnumber."'";
		$collection = $this->resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getOneUnUseKeyByProductid($productid)
	{
		$sqlString = "select id, product_id, digital_key 
					  from mitac_productkey 
					  where product_id = '".$productid."'
						and status = true
						and isused = false
						limit 1";
		$collection = $this->resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getUseKeyByCustomid($storeId,$customId)
	{
		$sqlString = "select x.customer_id, x.entity_id, x.status, my.product_id, my.digital_key, my.updated_at
						from
						(
							select so.customer_id, so.entity_id, so.status
							from sales_order so
							where 1=1 
								and so.customer_id = '".$customId."'
							and so.store_id = '".$storeId."'
							and so.status not in ('pending','closed','canceled')
						) x
						left join mitac_productkey my on my.order_id = x.entity_id
						where my.digital_key is not null
							and my.status is true
							and my.isused is true";
		$collection = $this->resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getSNByMicc($model_id)
	{
		$sqlString = "select mds.id, mds.model_id, mds.product_id, mds.product_name, mds.customer_id, mds.used, mds.launch_year
					from mitac_device_serials mds
					where mds.model_id = '".$model_id."'";
		$collection = $this->resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getCheckSNByorderid($productid, $order_id)
	{
		$sqlString = "select my.id, my.product_id, my.isused, my.order_id, my.digital_key
					from mitac_productkey my
					where my.product_id = '".$productid."' 
						and my.order_id = '".$order_id."'";
		$collection = $this->resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getKeyAmtByProductid($productid, $isused=0)
	{
		$sqlString = "select id, product_id, digital_key 
					  from mitac_productkey 
					  where product_id = '".$productid."'
						and status = true
						and isused = '".$isused."'";
		$collection = $this->resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getKeyData($id)
    {
        $sqlString = "select id, device_id , serialnumber 
					  from mitac_product 
                      where id = '".$id."'";
		$collection = $this->resource->getConnection();
		$result = $collection->fetchAll($sqlString);

        return $result;
    }

    public function getCustomerMap($CustomId)
    {
      $CustomerMaps = [];

      $StoreId = $this->storeManager->getStore()->getStoreId();

      if(!empty($StoreId) && !empty($CustomId))
      {
        $Mps = $this->keyhelper->getUseKeyByCustomid($StoreId, $CustomId);

        if(!empty($Mps))
        {
          $CustomerMaps = Array(
                            'status' => "200",
                            'message' => "Data is find"
                          );

          foreach ($Mps as $key => $value)
          {
            $BaseUrl         = $this->scopeConfig->getValue('web/unsecure/base_url');
            //--------------------------------------------------------------------------------//
            $imgproduct = $this->productRepositoryInterface->getById($value['product_id'],false,$StoreId);
            $productImageUrl = $BaseUrl.'media/catalog/product'.$imgproduct->getImage();
             
            if(!empty($productImageUrl) && strpos($productImageUrl,'productno_selection')==false)
            {
              $productImageUrl = str_replace($BaseUrl, '', $productImageUrl);
              $productImageUrl = str_replace('pub/media/catalog/product', '', $productImageUrl);
              $productImageUrl = str_replace('media/catalog/product', '', $productImageUrl);
              $img = $productImageUrl;
            }
            else
            {
              $img = '';
            }

            $CustomerMaps['data'][] = Array(
                                      'product_id'   => $value['product_id'],
                                      'product_name' => $value['product_name'],
                                      'product_img'  => $img,
                                      'digital_key'  => $value['digital_key'],
                                      'active_time'  => $value['updated_at']
                                    );
          }
        }
        else
        {
            $CustomerMaps = Array(
                              'status' => "407",
                              'message' => "Data Error"
                            );
        }
      }
      else
      {
          $CustomerMaps = Array(
                            'status' => "407",
                            'message' => "Data Error"
                          );
      }

      return Array($CustomerMaps);
    }

	public function getSoap($apiURL)
	{
		$writer = new \Zend\Log\Writer\Stream(BP.'/var/log/SyncMapSend.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);

		try
		{
			$client = new \SoapClient($apiURL, [
				'stream_context' => stream_context_create([
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
						'ciphers' => 'DEFAULT:!DH:DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5',
					],
				]),
			]);

			return $client;
		}
		catch (\Exception $e) 
		{
			$logger->info('Safe Camera Key getSoap Error : '.$e->getMessage());
		}
	}
	
	public function getOpenSession($apiURL,$apiAccont,$apiPassword)
	{
		$writer = new \Zend\Log\Writer\Stream(BP.'/var/log/SyncMapSend.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);

		try
		{
			$client = $this->getSoap($apiURL);
			
			$result = $client->openSession($apiAccont,$apiPassword);
		}
		catch (\Exception $e) 
		{
			$logger->info('Safe Camera Key getOpenSession Error : '.$e->getMessage());
			return null;
		}
		
		return $result;
	}

	public function getOneUnUseCameraSNByProductid($apiURL,$sessionId,$productid,$xmlURL)
	{
		$writer = new \Zend\Log\Writer\Stream(BP.'/var/log/SyncMapSend.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);

		$result = null;

		try
		{
			$client = $this->getSoap($apiURL);
			
			$result = $client->ecGetProductKey($sessionId,$productid,'EN-GB',$xmlURL);
		}
		catch (\Exception $e) 
		{
			$logger->info('Safe Camera Key getOneUnUseCameraSNByProductid Error : '.$e->getMessage());
			return null;
		}

		return $result;
	}

}
