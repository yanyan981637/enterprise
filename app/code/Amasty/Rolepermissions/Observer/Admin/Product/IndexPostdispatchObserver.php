<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Product;

use Magento\Framework\Event\ObserverInterface;

class IndexPostdispatchObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    private $view;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Amasty\Rolepermissions\Helper\Data $helper
     */
    private $helper;

    public function __construct(
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Amasty\Rolepermissions\Helper\Data $helper
    ) {
        $this->view = $view;
        $this->authorization = $authorization;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::save_products')
            || empty($this->helper->getAllowedSetIds())
        ) {
            $listBlock = $this->view->getLayout()->getBlock('products_list');
            if ($listBlock !== false) {
                $listBlock->updateButton('add_new', 'disabled', true);
            } else {
                $this->helper->redirectHome();
            }
        }
    }
}
