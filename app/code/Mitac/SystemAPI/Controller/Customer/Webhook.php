<?php
declare(strict_types=1);

namespace Mitac\SystemAPI\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\Context;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\ForwardFactory;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Session\SessionManager;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Framework\Registry;
use Mitac\SystemAPI\Helper\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Webhook extends Action implements CsrfAwareActionInterface
{
	/**
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
	protected $resultJsonFactory;

	/**
	 * @var \Magento\Framework\Controller\Result\ForwardFactory
	 */
	protected $resultForwardFactory;

	/**
	 * @var Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 * @var Magento\Customer\Api\AccountManagementInterface
	 */
	protected $customerAccountManagement;

	/**
	 * @var \Magento\Customer\Api\CustomerRepositoryInterface
	 */
	protected $customerRepository;

	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $customerFactory;

    /**
     * @var SessionManager
     */
    protected $coreSession;

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $registry;

	/**
	 * @var \Magento\Newsletter\Model\SubscriberFactory
	 */
	protected $subscriberFactory;

	/**
	 * @var \Magento\Newsletter\Model\SubscriptionManagerInterface
	 */
	protected $subscriptionManager;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storemanager;

	/**
	 * @var \Mitac\SystemAPI\Helper\Data
	 */
	protected $helperData;

	public function __construct(
		Context $context,
		JsonFactory $resultJsonFactory,
		ForwardFactory $resultForwardFactory,
		ScopeConfigInterface $scopeConfig,
		AccountManagementInterface $customerAccountManagement,
		CustomerRepositoryInterface $customerRepository,
		CustomerFactory $customerFactory,
		SessionManager $coreSession,
		Registry $registry,
		SubscriberFactory $subscriberFactory,
		SubscriptionManagerInterface $subscriptionManager,
		StoreManagerInterface $storemanager,
		Data $helperData
	)
	{
		parent::__construct($context);

		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_resultForwardFactory = $resultForwardFactory;
		$this->scopeConfig = $scopeConfig;
		$this->customerAccountManagement = $customerAccountManagement;
		$this->customerRepository = $customerRepository;
		$this->customerFactory = $customerFactory;
		$this->coreSession = $coreSession;
		$this->registry = $registry;
		$this->subscriberFactory = $subscriberFactory;
		$this->subscriptionManager = $subscriptionManager;
		$this->storemanager = $storemanager;
		$this->helperData = $helperData;

		$this->ScopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	}

	/**
	 * Redirect to checkout
	 *
	 * @return void
	 */
	public function execute()
	{
		$logFileName = 'Webhook.log';
		$logger = new \Mitac\SystemAPI\Model\Log('DEBUG', $logFileName);

		$logger->info("-----log webhook : get postback data : start");

		$returnArr = [
			"status" => "500",
			"flag" => false,
			"message" => ""
		];

		$resultJson = $this->_resultJsonFactory->create();

		try {
			// Block Non-Post Action
			if (!$this->getRequest()->isPost()) {
				$logger->info("-----log webhook : not a post action : **error**");

				$notPostdata = $this->getRequest()->getContent();
				$logger->info(print_r($notPostdata, true));

				$returnArr["status"] = "404";
				$returnArr["flag"] = false;
				$returnArr["message"] = "Not a post method, please correct it and retry!";

				$logger->info(print_r($returnArr, true));
				$resultJson->setData($returnArr);
				$logger->info("-----log webhook : get postback data : end");
				
				$resultForward = $this->_resultForwardFactory->create();
				return $resultForward->forward('noroute');
			}

			$webhookEnabled = $this->scopeConfig->getValue('mdt/general/webhook_enabled');
			$webhookToken = $this->scopeConfig->getValue('mdt/general/webhook_token');

			if ($webhookEnabled == true && !empty($webhookToken)) {
				$postdata = $this->getRequest()->getContent();
				$response = json_decode($postdata, true);
				$logger->info(print_r($response, true));

				if (array_key_exists('Secret', $response) && 
					array_key_exists('WebhookType', $response) && 
					($response['Secret'] == $webhookToken)) 
				{
					$logger->info("webhook-secret=".$response['Secret']);
					$logger->info("webhook-type=".$response['WebhookType']);

					if (array_key_exists('customerInfo', $response)) {
						$customerFromSystem = $response['customerInfo']['from_system'];
						$sysFromSystem = $this->scopeConfig->getValue('mdt/general/api_from_system');
					}

					switch ($response['WebhookType']) {
						# region disabled
							// case 'createcustomer':
							// 	$customerEmail = $response['Email'];
							// 	$customerFirstname = $response['customerInfo']['first_name'];
							// 	$customerLastname = $response['customerInfo']['last_name'];
							// 	$isNewsStatusApi = $response['is_news'];
							// 	$customerPswd = $response['Pswd'];

							// 	$logger->info('customerEmail='.print_r($customerEmail, true));
							// 	$logger->info('isNewsStatus='.print_r($isNewsStatusApi, true));

							// 	try {
							// 		$MagAccount = $this->customerAccountManagement->isEmailAvailable(trim($customerEmail), $this->storemanager->getStore()->getWebsiteId());
							// 		$logger->info("isEmailAvailableToUse=".(int) $MagAccount);
							// 		$logger->info("customerFromSystem=".$customerFromSystem);
							// 		$logger->info("sysFromSystem=".$sysFromSystem);

							// 		if ($MagAccount && ($customerFromSystem == $sysFromSystem)) {
							// 			$customer = $this->customerFactory->create();
									
							// 			$customer->setStoreId($this->storemanager->getStore()->getId());
							// 			$customer->setWebsiteId($this->storemanager->getStore()->getWebsiteId());
		
							// 			$customer->setEmail($customerEmail);
							// 			$customer->setFirstname($customerFirstname);
							// 			$customer->setLastname($customerLastname);
							// 			$customer->setPassword($customerPswd);

							// 			$customer->setConfirmation(true);
							// 			//set trigger_webhook false to Prevent observe from launching
							// 			$customer->setData('trigger_webhook', true);

							// 			// Save data
							// 			$customer->save();
		
							// 			$subscribeModel = $this->subscriberFactory->create();
							// 			//set trigger_webhook false to Prevent observe from launching
							// 			$subscribeModel->setData('trigger_webhook', true);
							// 			if ($isNewsStatusApi == 0) {
							// 				$subscribeModel = $this->subscriberFactory->create()->unsubscribeCustomerById($customer->getId());
							// 				$logger->info("customerId = ".$customer->getId()." has been unsubscribed.");
							// 			} else {
							// 				$this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
							// 				$logger->info("customerId = ".$customer->getId()." has been subscribed.");
							// 			}

							// 			$returnArr["status"] = "200";
							// 			$returnArr["flag"] = true;
							// 			$returnArr["message"] = "Get createcustomer!";
							// 		} else {
							// 			$returnArr["status"] = "200";
							// 			$returnArr["flag"] = true;
							// 			$returnArr["message"] = "Email Exists! Nothing To Do!";
							// 		}
							// 	} catch (\Exception $e) {
							// 		$logger->info("-----log webhook : " . print_r($e->getMessage(), true) . " : **error**");
							// 	}
							
							// 	break;
						# end disabled

						case 'updatecustomer':
							$customerEmail = $response['Email'];
							$isNewsStatusWebhook = $response['is_news'];
							$logger->info('customerEmail='.print_r($customerEmail, true));

							try {
								$MagAccount = $this->customerAccountManagement->isEmailAvailable(trim($customerEmail), $this->storemanager->getStore()->getWebsiteId());
								$logger->info("isEmailExistedInMagento=".(int) !$MagAccount);
								if (!$MagAccount) {
									$customer = $this->customerRepository->get($customerEmail);

									if ($customer->getId() && $customer->getWebsiteId()) {
										// Check first / last name
										if (array_key_exists('first_name', $response['customerInfo']) && 
											array_key_exists('last_name', $response['customerInfo'])) 
										{
											$customerFirstName = $response['customerInfo']['first_name'];
											$customerLastName = $response['customerInfo']['last_name'];
											if ($customer->getFirstname() != $customerFirstName || 
												$customer->getLastname() != $customerLastName) 
											{
												$customer->setFirstname($customerFirstName);
												$customer->setLastname($customerLastName);

												//set trigger_webhook true to Prevent observe from launching
												// $this->coreSession->setTriggerWebhookCustomer(true);

												$this->customerRepository->save($customer);
												$logger->info("customerId = ".$customer->getId().", the first / last name has change to ".
													$customerFirstName.", ".$customerLastName.".");
											} else {
												$logger->info("customerId = ".$customer->getId().", the first / last name has no need to change.");
											}
										}

										// Check Newsletter subscribe status
										$subscriber = $this->subscriberFactory->create()->loadByCustomerId($customer->getId());
										if ($subscriber->getSubscriberStatus() == 1)
											$isSubscribedState = "1";
										else 
											$isSubscribedState = "0";

										$logger->info('isNewsStatusWebhook='.print_r($isNewsStatusWebhook, true));
										$logger->info('isSubscribedState='.print_r($isSubscribedState, true));

										if ($isNewsStatusWebhook != $isSubscribedState) {
											//set trigger_webhook false to Prevent observe from launching
											$this->coreSession->setTriggerWebhookNewsletter(true);

											if ($isNewsStatusWebhook == 0) {
												$this->subscriptionManager->unsubscribeCustomer((int)$customer->getId(), (int)$customer->getStoreId());
												$logger->info("customerId = ".$customer->getId()." has been unsubscribed.");
											} else {
												$this->subscriptionManager->subscribeCustomer((int)$customer->getId(), (int)$customer->getStoreId());
												$logger->info("customerId = ".$customer->getId()." has been subscribed.");
											}
										} else {
											$logger->info("customerId = ".$customer->getId()." has no need to change subscribe status.");
										}
									}

									$returnArr["status"] = "200";
									$returnArr["flag"] = true;
									$returnArr["message"] = "Get updatecustomer!";
								} else {
									$returnArr["status"] = "200";
									$returnArr["flag"] = true;
									$returnArr["message"] = "Email Not Exists! Nothing To Do!";
								}
							} catch (\Exception $e) {
								$logger->info("-----log webhook : " . print_r($e->getMessage(), true) . " : **error**");
							}								
							
							break;

						case 'deletecustomer':
							$customerEmail = $response['Email'];
							$logger->info('customerEmail='.print_r($customerEmail, true));

							try {
								$MagAccount = $this->customerAccountManagement->isEmailAvailable(trim($customerEmail), $this->storemanager->getStore()->getWebsiteId());
								$logger->info("isEmailExistedInMagento=".(int) !$MagAccount);
								if (!$MagAccount) {
									$customer = $this->customerRepository->get($customerEmail);

									if ($customer->getId() && $customer->getWebsiteId()) {
										$this->registry->register('isSecureArea', true);
										$this->customerRepository->delete($customer);
										$logger->info("customerId = ".$customer->getId()." has been deleted.");
									} else {
										$logger->info("customer not exist in magento.");
									}

									$returnArr["status"] = "200";
									$returnArr["flag"] = true;
									$returnArr["message"] = "Get deletecustomer!";
								} else {
									$returnArr["status"] = "200";
									$returnArr["flag"] = true;
									$returnArr["message"] = "Email Not Exists! Nothing To Do!";
								}
							} catch (\Exception $e) {
								$logger->info("-----log webhook : " . print_r($e->getMessage(), true) . " : **error**");
							}

							break;

						case 'updatecustomeremail':
							$OriEmail = $response['OriEmail'];
							$NewEmail = $response['NewEmail'];
							$logger->info('Original customerEmail='.print_r($OriEmail, true));
							$logger->info('New customerEmail='.print_r($NewEmail, true));

							try {
								$MagAccount = $this->customerAccountManagement->isEmailAvailable(trim($OriEmail), $this->storemanager->getStore()->getWebsiteId());
								$MagAccountNew = $this->customerAccountManagement->isEmailAvailable(trim($NewEmail), $this->storemanager->getStore()->getWebsiteId());
								$logger->info("isOriEmailExistedInMagento=".(int) !$MagAccount);
								$logger->info("isNewEmailExistedInMagento=".(int) !$MagAccountNew);
								if (!$MagAccount && $MagAccountNew) {
									$customer = $this->customerRepository->get($OriEmail);

									if ($customer->getId() && $customer->getWebsiteId() && ($OriEmail !== $NewEmail)) {
										$customer->setEmail($NewEmail);

										//set trigger_webhook true to Prevent observe from launching
										// $this->coreSession->setTriggerWebhookCustomer(true);

										$this->customerRepository->save($customer);
										$logger->info("customerId = ".$customer->getId().", the email has change to ".$OriEmail.", ".$NewEmail.".");
									} else {
										$logger->info("customerId = ".$customer->getId().", the email has no need to change.");
									}

									$returnArr["status"] = "200";
									$returnArr["flag"] = true;
									$returnArr["message"] = "Get updatecustomeremail!";
								} else {
									$returnArr["status"] = "200";
									$returnArr["flag"] = true;
									$returnArr["message"] = "Email Not Exists! Nothing To Do!";
								}
							} catch (\Exception $e) {
								$logger->info("-----log webhook : " . print_r($e->getMessage(), true) . " : **error**");
							}

							break;

						default:
							$logger->info("-----log webhook : post data not correct, abandoned process : **error**");
							$returnArr["status"] = "401";
							$returnArr["flag"] = false;
							$returnArr["message"] = "Post data not correct, abandoned process!";
							break;
					}
				} else {
					$logger->info("-----log webhook : post data not correct, abandoned process : **error**");
					$returnArr["status"] = "401";
					$returnArr["flag"] = false;
					$returnArr["message"] = "Post data not correct, abandoned process!";
				}
			} else {
				$logger->info("-----log webhook : webhook receiver not enabled! : **error**");
				$returnArr["status"] = "200";
				$returnArr["flag"] = false;
				$returnArr["message"] = "Webhook receiver not enabled!";
			}

			$logger->info("-----log webhook : get postback data : end");

			$resultJson->setData($returnArr);
			return $resultJson;
		} 
		catch (\Throwable $e) {
			$returnArr["status"] = "400";
			$returnArr["flag"] = false;
			$returnArr["message"] = $e->getMessage();

			$logger->info("-----log webhook : " . print_r($e->getMessage(), true) . " : **exception**");
			$logger->info(print_r($returnArr, true));
			$resultJson->setData($returnArr);
			$logger->info("-----log webhook : get postback data : end");
			return $resultJson;
		}

	}

	public function createCsrfValidationException(
		RequestInterface $request
	): ?InvalidRequestException {
		return null;
	}

	public function validateForCsrf(RequestInterface $request): ?bool {
		return true;
	}

}
