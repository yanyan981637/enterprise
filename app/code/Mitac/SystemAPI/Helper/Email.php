<?php
namespace Mitac\SystemAPI\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

use Mitac\SystemAPI\Helper\Data;

class Email extends AbstractHelper
{
	protected $_scope;
	protected $inlineTranslation;
	protected $transportBuilder;
	protected $senderResolver;
	protected $storeManager;
	protected $scopeStore;
	protected $helpData;

	public function __construct
	(
		Context $context,
		ScopeConfigInterface $scopeConfig,
		StateInterface $inlineTranslation,
		TransportBuilder $transprotbuilder,
		SenderResolverInterface $senderResolver,
		StoreManagerInterface $storeManager,
		Data $helpData
	)
	{
		$this->_scope = $scopeConfig;
		$this->inlineTranslation = $inlineTranslation;
		$this->transportBuilder = $transprotbuilder;
		$this->senderResolver = $senderResolver;
		$this->storeManager = $storeManager;
		$this->helpData = $helpData;
		$this->scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	}

	/*----------------------------------------------------------------------------*/
	/*********** Send Order Related Email Sub function ******************/
	public function sendEmail($store, $renderdata, array $receiver = null, $templateid = null )
	{
		$sendemail   = $this->_scope->getValue('SyncERP/ERPBase/api_sendemail', $this->scopeStore);
		$adminemail  = $this->_scope->getValue('SyncERP/ERPOrder/api_adminemail', $this->scopeStore);
		// $serviceemail  = $this->_scope->getValue('SyncERP/ERPOrder/api_serviceemail', $this->scopeStore);

		if (!$templateid) {
			$templateid  = $this->_scope->getValue('SyncERP/ERPOrder/notify_email', $this->scopeStore);
		}

		$sender = $this->senderResolver->resolve($sendemail);

		if (!empty($adminemail)) {
			$adminSentToEmail = array_map('trim', explode(',',$adminemail));
		}

		$transportObject = new \Magento\Framework\DataObject($renderdata);

		$this->inlineTranslation->suspend();
		$transport = $this->transportBuilder->setTemplateIdentifier($templateid)
			->setTemplateOptions(
				[
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
					'store' => $store
				]
			)
			->setTemplateVars($transportObject->getData())
			->setFrom($sender);

		if (!empty($receiver)) {
			$transport->addTo($receiver['email'], $receiver['name']);
			if (!empty($adminSentToEmail)) {
				$transport->addBcc($adminSentToEmail);
			}
		} else {
			if (!empty($adminSentToEmail)) {
				$transport->addTo($adminSentToEmail);
			}
		}

		try {
			$transport->getTransport()->sendMessage();
			$this->inlineTranslation->resume();
			return true;
		} catch (\Exception $e) {
			return $e->getMessage();    
		}
	}

	public function sendServiceEmail($store, $renderdata, array $receiver = null, $templateid = null)
	{
		$sendemail   = $this->_scope->getValue('SyncERP/ERPBase/api_sendemail', $this->scopeStore);
		$serviceemail  = $this->_scope->getValue('SyncERP/ERPOrder/api_serviceemail', $this->scopeStore);

		if (!$templateid) {
			$templateid  = $this->_scope->getValue('SyncERP/ERPOrder/notify_email', $this->scopeStore);
		}

		$sender = $this->senderResolver->resolve($sendemail);

		if (!empty($serviceemail)) {
			$sentToEmail = array_map('trim', explode(',',$serviceemail));
		}

		$transportObject = new \Magento\Framework\DataObject($renderdata);

		$this->inlineTranslation->suspend();
		$transport = $this->transportBuilder->setTemplateIdentifier($templateid)
			->setTemplateOptions(
				[
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
					'store' => $store
				]
			)
			->setTemplateVars($transportObject->getData())
			->setFrom($sender);

		if (!empty($receiver)) {
			$transport->addTo($receiver['email'], $receiver['name']);
			if (!empty($sentToEmail)) {
				$transport->addBcc($sentToEmail);
			}
		} else {
			if (!empty($sentToEmail)) {
				$transport->addTo($sentToEmail);
			}
		}

		try {
			$transport->getTransport()->sendMessage();
			$this->inlineTranslation->resume();
			return true;
		} catch (\Exception $e) {
			return $e->getMessage();    
		}
	}

	public function sendMegaEmail($store, array $receiver, $templateid, array $postbackContent)
	{
		$senderName = $this->_scope->getValue('trans_email/ident_general/name', $this->scopeStore);
		$senderEmail = $this->_scope->getValue('trans_email/ident_general/email', $this->scopeStore);

		//Sender User
		$sender = [
			'name'  => $senderName,
			'email' => $senderEmail
		];

		$adminEmail = $this->_scope->getValue('megabank/general/notify_admin_email', $this->scopeStore);
		$sentToEmail = array_map('trim', explode(',', $adminEmail));

		$this->inlineTranslation->suspend();
		$transport = $this->transportBuilder->setTemplateIdentifier($templateid)
			->setTemplateOptions(
				[
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
					'store' => $store
				]
			)
			->setTemplateVars($postbackContent)
			->setFrom($sender)
			->addTo($receiver['email'], $receiver['name']);

		if ($adminEmail) {
			$transport->addBcc($sentToEmail);
		}

		try 
		{
			$transport->getTransport()->sendMessage();
			$this->inlineTranslation->resume();
			return true;
		} 
		catch (\Exception $e) 
		{
			return $e->getMessage();	
		}
	}
	/*********** Send Order Related Email Sub function ******************/
	/*----------------------------------------------------------------------------*/

	/*----------------------------------------------------------------------------*/
	/*********** ERP Sync Stock Related Email Main function *************/
	public function sendLowStockEmail($notifyArr)
	{
		$sendemail 	 = $this->_scope->getValue('SyncERP/ERPBase/api_sendemail', $this->scopeStore);
		$adminemail  = $this->_scope->getValue('SyncERP/ERPStock/api_adminemail', $this->scopeStore);
		$salesemail  = $this->_scope->getValue('SyncERP/ERPStock/api_salesemail', $this->scopeStore);
		$templateId  = $this->_scope->getValue('SyncERP/ERPStock/notify_email', $this->scopeStore);

		$sender = $this->senderResolver->resolve($sendemail);
		$sentToEmail = array();

		if (!empty($adminemail)) {
			$sentToEmail = array_merge($sentToEmail, array_map('trim', explode(',',$adminemail)));
		}

		if (!empty($salesemail)) {
			$sentToEmail = array_merge($sentToEmail, array_map('trim', explode(',',$salesemail)));
		}

		$this->inlineTranslation->suspend();

		$transport = $this->transportBuilder->setTemplateIdentifier($templateId)
		->setTemplateOptions(
			[
				'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
				'store' => 15
			]
		)
		->setTemplateVars([
			'subject' => 'Notify Admin for Low Stock (Admin)',
			'currentDate' => $this->helpData->converToTz(Date('Y-m-d H:i:s'), $this->storeManager->getStore()->getCode(), "Y-m-d"),
			'stockrow' => $this->lowStockToHtml($notifyArr),
		])
		->setFrom($sender)
		->addTo($sentToEmail);

		try 
		{
			$transport->getTransport()->sendMessage();
			$this->inlineTranslation->resume();
			return true;
		} 
		catch (\Exception $e) 
		{
			return $e->getMessage();
		}
	}

	public function sendShortStockEmail($notifyArr)
	{
		$sendemail 	 = $this->_scope->getValue('SyncERP/ERPBase/api_sendemail', $this->scopeStore);
		$adminemail  = $this->_scope->getValue('SyncERP/ERPStock/short_api_adminemail', $this->scopeStore);
		$templateId  = $this->_scope->getValue('SyncERP/ERPStock/short_notify_email', $this->scopeStore);

		$sender = $this->senderResolver->resolve($sendemail);
		$sentToEmail = array_map('trim', explode(',', $adminemail));

		$this->inlineTranslation->suspend();

		$transport = $this->transportBuilder->setTemplateIdentifier($templateId)
		->setTemplateOptions(
			[
				'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
				'store' => 15
			]
		)
		->setTemplateVars([
			'subject' => 'Notify Admin for Short Stock (Admin)',
			'stockrow' => $this->shortStockToHtml($notifyArr),
		])
		->setFrom($sender)
		->addTo($sentToEmail);

		try 
		{
			$transport->getTransport()->sendMessage();
			$this->inlineTranslation->resume();
			return true;
		} 
		catch (\Exception $e) 
		{
			return $e->getMessage();
		}
	}

	public function sendLockStockEmail($notifyArr)
	{
		$sendemail 	 = $this->_scope->getValue('SyncERP/ERPBase/api_sendemail', $this->scopeStore);
		$adminemail  = $this->_scope->getValue('SyncERP/ERPStock/short_api_adminemail', $this->scopeStore);
		$templateid  = $this->_scope->getValue('SyncERP/ERPStock/lockstock_notify_email', $this->scopeStore);

		$sender = $this->senderResolver->resolve($sendemail);
		$sentToEmail = array_map('trim', explode(',', $adminemail));

		$this->inlineTranslation->suspend();

		$dataToEmail = [
			'subject' => 'Notify Admin for Lock Stock (Admin)',
			'data' => $this->lockStockToHtml($notifyArr)
		];

		$transportObject = new \Magento\Framework\DataObject($dataToEmail);

		$this->inlineTranslation->suspend();
		$transport = $this->transportBuilder->setTemplateIdentifier($templateid)
			->setTemplateOptions(
				[
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
					'store' => 15
				]
			)
			->setTemplateVars($transportObject->getData())
			->setFrom($sender)
			->addTo($sentToEmail);

		try 
		{
			$transport->getTransport()->sendMessage();
			$this->inlineTranslation->resume();
			return true;
		} 
		catch (\Exception $e) 
		{
			return $e->getMessage();
		}
	}
	/*********** ERP Sync Stock Related Email Main function *************/
	/*----------------------------------------------------------------------------*/


	/*----------------------------------------------------------------------------*/
	/*********** ERP Sync Stock Related Email Sub function **************/
	protected function lowStockToHtml($notifyArr)
	{
		$returnHtml = '';

		foreach ($notifyArr as $Rows) 
		{
			$returnHtml .= '
			<tr>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["sku"].'</td>
				<td style="text-align: left; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["product-name"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["mag-stock-level"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["qty"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["mag-qty"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["erp-unship"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["arrived-alert"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows['isinerp'].'</td>
			</tr>';
		}

		return $returnHtml;
	}

	protected function shortStockToHtml($notifyArr)
	{
		$returnHtml = '';

		foreach ($notifyArr as $Rows) 
		{
			$returnHtml .= '
			<tr>
				<td style="text-align: center; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["sku"].'</td>
				<td style="text-align: right; vertical-align: middle; background-color: white; border:1px solid;">'.$Rows["qty"].'</td>
			</tr>';
		}

		return $returnHtml;
	}

	protected function lockStockToHtml($notifyArr)
	{
		$returnHtml = '';

		$returnHtml .= '<table style="width: 200px;">
		<tr>
			<th style="text-align: center; vertical-align: middle; background-color: white;border: 1px solid #1C6EA4;">System_ID</th>
			<th style="text-align: center; vertical-align: middle; background-color: white;border: 1px solid #1C6EA4;">Order_ID</th>
			<th style="text-align: center; vertical-align: middle; background-color: white;border: 1px solid #1C6EA4;">Part_Number</th>
		</tr>';

		foreach ($notifyArr as $rows) {
			$returnHtml .= '
			<tr>
				<td style="text-align: center; vertical-align: middle; background-color: white;border: 1px solid #1C6EA4;">'.$rows["System_ID"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white;border: 1px solid #1C6EA4;">'.$rows["Order_ID"].'</td>
				<td style="text-align: center; vertical-align: middle; background-color: white;border: 1px solid #1C6EA4;">'.$rows['Part_Number'].'</td>
			</tr>';
		}

		$returnHtml .= '</table>';

		return $returnHtml;
	}
	/*********** ERP Sync Stock Related Email Sub function **************/
	/*----------------------------------------------------------------------------*/

}