<?php
namespace Mitac\Homepage\Controller\Adminhtml\Index;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
	protected $resultJsonFactory;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	) 
	{
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
	}

	/**
	 * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
	 */
	public function execute()
	{
		$manageEntity = __('Manage Block');

		/** @var Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->addBreadcrumb($manageEntity, $manageEntity);
		$resultPage->getConfig()->getTitle()->prepend($manageEntity);

		return $resultPage;
	}

}
