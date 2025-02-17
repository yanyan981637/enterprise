<?php
namespace Mitac\CustomCatalog\Plugin\Compare\Controller;

class CheckCompare
{
	/**
	* @var \Magento\Catalog\CustomerData\CompareProducts
	*/
	protected $compareProducts;

	/**
	* @var \Magento\Framework\App\RequestInterface
	*/
	protected $request;

	/**
	* @var \Magento\Catalog\Api\ProductRepositoryInterface
	*/
	protected $productRepository;

	/**
	* @var \Magento\Framework\Message\ManagerInterface
	*/
	protected $messageManager;

	/**
	* @var \Magento\Framework\Controller\ResultFactory
	*/
	protected $resultFactory;

	/**
	* @var \Magento\Framework\App\Response\RedirectInterface
	*/
	protected $redirect;

	protected $LIMIT_TO_COMPARE_PRODUCTS = 4;

	/**
	* @param \Magento\Catalog\CustomerData\CompareProducts $compareProducts,
	* @param \Magento\Framework\App\RequestInterface $request,
	* @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
	* @param \Magento\Framework\Message\ManagerInterface $messageManager,
	* @param \Magento\Framework\Controller\ResultFactory $resultFactory,     
	* @param \Magento\Framework\App\Response\RedirectInterface $redirect 
	*/
	public function __construct(
		\Magento\Catalog\CustomerData\CompareProducts $compareProducts,
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Controller\ResultFactory $resultFactory,     
		\Magento\Framework\App\Response\RedirectInterface $redirect     
	) 
	{
		$this->compareProducts = $compareProducts;
		$this->request = $request;
		$this->productRepository = $productRepository;
		$this->messageManager = $messageManager;
		$this->resultFactory = $resultFactory;
		$this->redirect = $redirect;
	}

/**
 * check if product can be add to compare list
 */
	public function aroundExecute(\Magento\Catalog\Controller\Product\Compare\Add $subject, $proceed) 
	{
		$resultRedirect = $this->resultFactory->create(
			\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
		);
		$productIdtoBeAdd = $this->request->getParam('product');
		$productRepository = $this->productRepository->getById($productIdtoBeAdd);
		$newAtrributeSet = $productRepository->getAttributeSetId();
		$compareCollection = $this->compareProducts->getSectionData();
		$oldAtrributeSets = [];

		foreach($compareCollection['items'] as $compareList){
			$oldAtrributeSets[] = $this->productRepository->getById($compareList['id'])->getAttributeSetId();
		} 
		if(count($oldAtrributeSets) >= $this->LIMIT_TO_COMPARE_PRODUCTS){
			$this->messageManager->addWarningMessage(__('Sorry. Please note that you cannot compare more than %num items at a time.',
			[
				'num' => $this->LIMIT_TO_COMPARE_PRODUCTS
			])
			);

			$result = $resultRedirect->setUrl($this->redirect->getRefererUrl());
		}
		else if(count($oldAtrributeSets) > 0 && !in_array($newAtrributeSet, $oldAtrributeSets))
		{
			$this->messageManager->addWarningMessage(__('The product %s1 can not add to Comparison List.', 
				[
					's1'=>$productRepository->getName(),
				])
			);

			$result = $resultRedirect->setUrl($this->redirect->getRefererUrl());
		} 
		else 
		{
			$result = $proceed();
		}

		return $result;
	}

}