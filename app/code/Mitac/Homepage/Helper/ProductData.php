<?php
namespace Mitac\Homepage\Helper;

class ProductData extends \Magento\Framework\App\Helper\AbstractHelper
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


	public function getPageList($storeId)
	{
		$sqlString = "SELECT cp.page_id, cp.title, cp.identifier FROM cms_page as cp ";
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getCategoryList($storeId)
	{
		$sqlString = "SELECT ccev.entity_id, cct.value as title, ccev.value FROM catalog_category_entity_varchar AS ccev ";
		$sqlString .= "JOIN (SELECT entity_id, value FROM catalog_category_entity_varchar WHERE attribute_id = 41) AS cct ON ccev.entity_id = cct.entity_id ";
		$sqlString .= "JOIN (SELECT entity_id FROM catalog_category_entity_int WHERE attribute_id = 42 AND VALUE = 1) AS cca ON ccev.entity_id = cca.entity_id ";
		$sqlString .= "WHERE ccev.attribute_id = 57 ";
		$sqlString .= "ORDER BY entity_id ";

		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getBlogCategoryList($storeId)
	{
		$sqlString = "SELECT category_id, name, url_key ";
		$sqlString .= "FROM mageplaza_blog_category ";
		$sqlString .= "WHERE `path` != '1' and enabled = true ";
		$sqlString .= "ORDER BY category_id ";

		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

}
