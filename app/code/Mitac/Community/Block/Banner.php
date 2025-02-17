<?php
namespace Mitac\Community\Block;

class Banner extends \Magento\Framework\View\Element\Template
{
	protected $storeManager;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Cms\Model\Page $page,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Registry $registry,
		array $data = []
	) 
	{
		$this->_page = $page;
		$this->_storeManager = $storeManager;
		$this->_registry = $registry;
		parent::__construct($context, $data);
	}

	public function page()
	{
		return $this->_page;
	}

	public function getMediaUrl()
	{

		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		return $mediaUrl;	
	}

	public function getStore()
	{

		return $this->_storeManager->getStore();
	}

	public function getCurrentCategory()
	{
		return $this->_registry->registry('current_category');
	}
}
