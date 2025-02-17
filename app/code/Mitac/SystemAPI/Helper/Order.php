<?php
namespace Mitac\SystemAPI\Helper;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
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
		$this->_resource = $resource;
		$this->_store = $storeManager;
		$this->_scope = $scopeConfig;
	}

	public function getOrderStatusHistory($order_id, $status)
	{
	    $Str = '';

	    if(!empty($status))
	      $Str .= " and status = '".$status."'";

	    $sql = "select parent_id, comment, status 
	            from sales_order_status_history
	            where parent_id = '".$order_id."'
	               ".$Str."
	            order by entity_id";
	    $collection = $this->_resource->getConnection();
	    $result = $collection->fetchAll($sql);

	    return $result;
	}

}
