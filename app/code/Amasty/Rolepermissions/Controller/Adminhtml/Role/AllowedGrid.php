<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Controller\Adminhtml\Role;

use Magento\Backend\App\Action;

class AllowedGrid extends Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);

        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_coreRegistry = $registry;
    }

    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('amrolepermissions.allowed_roles.grid')
            ->setAllowedRoles($this->getRequest()->getPost('amrolepermissions_allowed_roles', []));

        return $resultLayout;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_User::acl_roles');
    }
}
