<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Rule;

use Magento\Framework\Event\ObserverInterface;

class PrepareObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Amasty\Rolepermissions\Model\RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        \Amasty\Rolepermissions\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
        $this->ruleFactory = $ruleFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  \Amasty\Rolepermissions\Model\Rule $rule */
        $rule = $this->ruleFactory->create();
        if ($rid = (int) $observer->getRequest()->getParam('rid')) {
            $rule->load($rid, 'role_id');
        }
        $this->coreRegistry->register('amrolepermissions_current_rule', $rule, true);
    }
}
