<?php
namespace Mitac\SystemAPI\Helper;

class Sale extends \Magento\Framework\App\Helper\AbstractHelper
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

	public function getCouponTotal($ruleid, $exdate=null)
	{
		$Str = '';

		$sqlString = "select COUNT(code) as count 
					  from salesrule_coupon 
					  where rule_id IN ('".$ruleid."')
					  	and is_send = 0
					  	and (expiration_date is null or CONVERT(expiration_date , DATE) > '".$exdate."')";
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getCoupon($ruleid, $issend=null, $timeuse=null, $exdate=null)
	{
		$Str = ''; $DateStr = '';
		
		if(isset($issend))
			$Str .= "and is_send = '".$issend."'";
		if(isset($timeuse))
			$Str .= "and times_used = '".$timeuse."'";

		$sqlString = "select coupon_id , rule_id, code, expiration_date
					  from salesrule_coupon 
					  where rule_id IN ('".$ruleid."')
							".$Str."
					  		and (expiration_date is null or CONVERT(expiration_date , DATE) > '".$exdate."')
					  order by coupon_id 
					  limit 1";
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

	public function getTradeCouponCheck($code, $ruleid)
	{
		$sqlString = "select coupon_id, rule_id, code
					  from salesrule_coupon 
					  where rule_id IN ('".$ruleid."')
						and code = '".trim($code)."'
					  group by coupon_id , rule_id, code";
		$collection = $this->_resource->getConnection();
		$result = $collection->fetchAll($sqlString);

		return $result;
	}

}
