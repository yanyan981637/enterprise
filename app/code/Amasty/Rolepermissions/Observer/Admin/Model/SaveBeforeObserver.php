<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Model;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;

class SaveBeforeObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getModuleName() == 'api') {
            return;
        }

        /** @var AbstractModel $object */
        $object = $observer->getObject();

        if (is_a($object, \Dotdigitalgroup\Email\Model\Review::class)) {
            return;
        }
        $rule = $this->helper->currentRule();

        if ($rule && $rule->getScopeStoreviews()) {
            if ($object->getId()) {
                $this->helper->restrictObjectByStores($object->getOrigData());
            }

            $this->helper->alterObjectStores($object);
        }
    }
}
