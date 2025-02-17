<?php
namespace Mitac\Homepage\Helper;

class StoreData extends \Magento\Framework\App\Helper\AbstractHelper
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

	public function getStoreData($storeId)
	{
		$sqlString = 'select wb.website_id, if(wb.website_id = 0,"All Store", wb.name) as wbname, sg.group_id, sg.name as groupname, s.store_id , if(wb.website_id = 0,"All Store View", s.name) as stroename ';
		$sqlString .= 'from store_website as wb ';
		$sqlString .= 'left join store_group as sg on sg.website_id  = wb.website_id ';
		$sqlString .= 'left join store as s on s.website_id = sg.website_id ';
		$sqlString .= 'WHERE s.store_id = '.$storeId.' ';
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function deleteBannerStore($bannerId)
	{
		$sqlString = 'DELETE FROM mitac_homebanner_stores WHERE banners_id = '.$bannerId;
		$collection = $this->_resource->getConnection();
		$result = $collection->exec($sqlString);

		return $result;
	}

	public function InsertBannerStore($bannerId, $storesArr)
	{
		$sqlString = 'INSERT INTO mitac_homebanner_stores (banners_id, stores_id) VALUES ';
		foreach ($storesArr as $keys => $values)
		{
			$sqlString .= '('.$bannerId.','.$values.'),';
			if ($values == 0)
			{
				break;
			}
		}
		$sqlString = substr_replace($sqlString, ';', -1);
		$collection = $this->_resource->getConnection();
		$result = $collection->exec($sqlString);
		
		return $sqlString;
	}

	public function getStores($bannerId)
	{
		$sqlString ='SELECT stores_id FROM mitac_homebanner_stores WHERE banners_id = '.$bannerId;
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);
		return $result;
	}

	public function getStoreSortData($Store_id, $block_type)
	{
		$sqlString = 'SELECT * FROM mitac_homebanners WHERE stores_id = '.$Store_id.' AND type = "'.$block_type.'" UNION SELECT * FROM mitac_homebanners WHERE stores_id = 0 AND type = "'.$block_type.'"';
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);
		return $result;
	}

	public function deletePageIdenifier($bannerId)
	{
		$sqlString = 'DELETE FROM mitac_homebanner_pages WHERE banners_id = '.$bannerId;
		$collection = $this->_resource->getConnection();
		$result = $collection->exec($sqlString);
		return $result;
	}

	public function InsertPageIdenifier($bannerId, $PageIdentifier)
	{
		$sqlString = 'INSERT INTO mitac_homebanner_pages (banners_id, identifier) VALUES ';
		
		/*
		foreach ($PageIdentifier as $keys => $values)
		{
			$PageIdentifierKeyArr = explode('<=>',$values);
			if(!empty($PageIdentifierKeyArr[1]))
			{
				$PageIdentifierKey = $PageIdentifierKeyArr[1];
			}
			echo $PageIdentifierKey;

			$sqlString .= '('.$bannerId.',"'.$PageIdentifierKey.'"),';
		}
		*/

		$sqlString .= '('.$bannerId.',"'.$PageIdentifier.'"),';
		$sqlString = substr_replace($sqlString, ';', -1);
		$collection = $this->_resource->getConnection();
		$result = $collection->exec($sqlString);
		return $sqlString;
	}

	public function getPageIdenifier($bannerId)
	{
		$sqlString ='SELECT identifier FROM mitac_homebanner_pages WHERE banners_id = '.$bannerId;
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);
		return $result;
	}

	public function getPagesData($identifier)
	{
		$sqlString = 'SELECT "Cms Page" as pagetype, title FROM cms_page WHERE identifier in ('.$identifier.') UNION SELECT "Categories" as pagetype, cct.value as title FROM catalog_category_entity_varchar AS ccev JOIN (SELECT entity_id, value FROM catalog_category_entity_varchar WHERE attribute_id = 41) AS cct ON ccev.entity_id = cct.entity_id JOIN (SELECT entity_id FROM catalog_category_entity_int WHERE attribute_id = 42 AND VALUE = 1) AS cca ON ccev.entity_id = cca.entity_id WHERE ccev.attribute_id = 57 AND ccev.value in ('.$identifier.') ORDER BY pagetype, title';
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);
		return $result;
	}

	public function getBlogData($identifier)
	{
		$sqlString = 'SELECT "Blog Category" as pagetype, name as "title" FROM mageplaza_blog_category WHERE category_id in ('.$identifier.') ORDER BY category_id, name';
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);
		return $result;
	}


}
