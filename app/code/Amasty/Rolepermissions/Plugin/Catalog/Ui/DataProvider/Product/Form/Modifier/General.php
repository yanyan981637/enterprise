<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

class General
{
    /** @var \Amasty\Rolepermissions\Helper\Data $helper */
    protected $helper;

    /**
     * General constructor.
     * @param \Amasty\Rolepermissions\Helper\Data $helper
     */
    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function afterModifyMeta($subject, $result)
    {
        $allowedAttributeCodes = $this->helper->getAllowedAttributeCodes();
        if (!is_array($allowedAttributeCodes)) {
            $allowedAttributeCodes = [];
        }
        array_walk(
            $allowedAttributeCodes,
            function (&$value) {
                $value = 'container_' . $value;
            }
        );
        $result = $this->_removeRestrictedAttributes($allowedAttributeCodes, $result);
        $result = $this->_removeEmptyTabs($result);
        if (!empty($allowedAttributeCodes) && isset($result['product-details']['children']['attribute_set_id'])) {
            $result['product-details']['children']['attribute_set_id']['arguments']['data']['config']['visible'] = 0;
        }

        return $result;
    }

    protected function _removeRestrictedAttributes($allowedAttributeCodes, $result)
    {
        $notRemoveKeys = [
            'container_links',
            'container_samples',
            'container_custom_design',
            'container_custom_layout'
        ];

        $additionalCheckKeys = [
            'quantity_and_stock_status_qty'
        ];

        if (empty($allowedAttributeCodes)) {
            return $result;
        }

        foreach ($result as $key => $value) {
            if (!in_array($key, $allowedAttributeCodes)
                && (
                    (strpos($key, 'container_') === 0 && !in_array($key, $notRemoveKeys))
                    || in_array($key, $additionalCheckKeys)
                )
            ) {
                unset($result[$key]);
            } else {
                if (is_array($value)) {
                    $result[$key] = $this->_removeRestrictedAttributes($allowedAttributeCodes, $value);
                }
            }
        }

        return $result;
    }

    protected function _removeEmptyTabs($result)
    {
        foreach ($result as $key => $value) {
            if (isset($value['children']) && empty($value['children'])) {
                unset($result[$key]);
            }
        }

        return $result;
    }
}
