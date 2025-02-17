<?php
namespace Mitac\Community\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;

class Data extends AbstractHelper
{
	protected $resource;
	protected $scopeConfig;
	private $_coreRegistry;
	private $_emailConfig;
	protected $_templatesFactory;

	public function __construct(
		ResourceConnection $resource,
		Registry $coreRegistry,
		CollectionFactory $templatesFactory,
		Config $emailConfig,
		ScopeConfigInterface $scopeConfig
	)
	{
		$this->_resource = $resource;
		$this->_scope = $scopeConfig;
		$this->_coreRegistry = $coreRegistry;
		$this->_emailConfig = $emailConfig;
		$this->_templatesFactory = $templatesFactory;
	}

	public function getSelectTemplateArray()
	{
		if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
			$collection = $this->_templatesFactory->create();
			$collection->load();
			$this->_coreRegistry->register('config_system_email_template', $collection);
		}
		$options = $collection->toOptionArray();
		return $options;
	}
}
