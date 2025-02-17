<?php
namespace Mitac\Homepage\Controller\Adminhtml\Slider;

use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
	const ADMIN_RESOURCE = 'Mitac_Homepage::slider_view';

	protected $resultPageFactory;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	) 
	{
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	/**
	 * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
	 */
	public function execute()
	{
		$manageEntity = __('Manage Slider Block');

		/** @var Page $resultPage */
		$resultPage = $this->resultPageFactory->create();

		$resultPage->addBreadcrumb($manageEntity, $manageEntity);
		$resultPage->addBreadcrumb($manageEntity, $manageEntity);
		$resultPage->getConfig()->getTitle()->prepend($manageEntity);

		return $resultPage;
	}

}
