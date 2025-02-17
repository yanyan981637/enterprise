<?php
namespace Mitac\Homepage\Helper;

class ImageData extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $resource;
	protected $storeManager;
	protected $scopeConfig;

	public function __construct(
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{
		$this->_resource = $resource;
		$this->_store = $storeManager;
		$this->_scope = $scopeConfig;
	}
	
	public function getImgData($bannerId)
	{
		$sqlString = 'SELECT ban.banners_id, ban.sort_id, ban.img ';
		$sqlString .= 'FROM mitac_homebanners ban ';
		$sqlString .= 'WHERE ban.banners_id = '.$bannerId.' ';
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function voidImgData($bannerId)
	{
		$sqlString = 'UPDATE mitac_homebanners ';
		$sqlString .= 'SET img = "" ';
		$sqlString .= 'WHERE banners_id = '.$bannerId.' ';
		$collection = $this->_resource->getConnection();
		$result = $collection->exec($sqlString);

		return $result;
	}

}
