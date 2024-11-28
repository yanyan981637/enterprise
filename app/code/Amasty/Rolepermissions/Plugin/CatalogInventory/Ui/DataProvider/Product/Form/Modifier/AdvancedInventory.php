<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\CatalogInventory\Ui\DataProvider\Product\Form\Modifier;

class AdvancedInventory
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function aroundModifyData(
        \Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory $subject,
        \Closure $proceed,
        array $data
    ) {
        $allowedCodes = $this->helper->getAllowedAttributeCodes();

        if ($allowedCodes === true || in_array('quantity_and_stock_status', $allowedCodes)) {
            $data = $proceed($data);
        }

        return $data;
    }
}
