<?php
namespace Mitac\SystemAPI\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
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

	/**
	 * @var TimezoneInterface
	 */
	protected $localeDate;

	public function __construct
	(
		ResourceConnection $resource,
		StoreManagerInterface $storeManager,
		ScopeConfigInterface $scopeConfig,
		TimezoneInterface $localeDate,
		TransactionSearchResultInterfaceFactory $paymentrans
	)
	{
		$this->_resource = $resource;
		$this->_store = $storeManager;
		$this->_scope = $scopeConfig;
		$this->_dbConnection = $this->_resource->getConnection();
		$this->_localeDate = $localeDate;
		$this->_paymentrans = $paymentrans;
	}

	public function converToTz($datetime, $toStoreCode = null, $format = "Y-m-d H:i:s") 
	{
		$fromTz = $this->_localeDate->getDefaultTimezone();
		$toTz = $this->_localeDate->getConfigTimezone('store', $toStoreCode);

		$date = new \DateTime($datetime, new \DateTimeZone($fromTz));

		if (!empty($toStoreCode)) {
			$date->setTimezone(new \DateTimeZone($toTz));
		}

		$dateTimeAsTimeZone = $date->format($format);

		return $dateTimeAsTimeZone;
	}

	public function getCurrentLocaleDateTime($currentDateTime = null, string $format = "Y-m-d H:i:s") 
	{
		try {
			switch (gettype($currentDateTime)) {
				case 'string':
				case 'object':
					$currentDateTime = new \DateTime($currentDateTime);
					break;

				default:
					$currentDateTime = new \DateTime(date("Y-m-d H:i:s"));
					break;
			}
		} catch (\Throwable $e) {
			$currentDateTime = new \DateTime(date("Y-m-d H:i:s"));
		}

		$dateTimeAsTimeZone = $this->_localeDate->date($currentDateTime)->format($format);

		return $dateTimeAsTimeZone;
	}

	public function getPaymentTransaction($order) 
	{
		if (!$order) {
			return false;
		}

		$transactionData = [];

		$transaction = $this->_paymentrans->create()
			->addOrderIdFilter($order->getId())
			->getLastItem();

		if (!empty($transaction)) {
			if ($transaction->getData('txn_type') == 'capture') {

				$transactionData['transId'] = $transaction->getData('txn_id');
				$transactionData['paymentMethod'] = $order->getPayment()->getMethod();
				$transactionData['transTime'] = $this->converToTz(
					$transaction->getData('created_at'), 
					$order->getStore()->getCode(), 
					"Y-m-d H:i:s"
				);

				return $transactionData;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function transaction($sqls) 
	{
		try {
			$this->_dbConnection->beginTransaction();

			foreach ($sqls as $sql) {
				$result[] = $this->_dbConnection->query($sql);
			}

			$this->_dbConnection->commit();

			$result = true;

		} catch(\Exception $e) {
			$this->_dbConnection->rollBack();
			// echo $e->getMessage();
			$result = $e->getMessage();
		}

		return $result;
	}

}
