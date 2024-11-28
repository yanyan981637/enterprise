<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */
namespace Magezon\ProductLabels\Model\Config\Source;

class RoundingMethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
        	['value' => 'round', 'label' => __('Normal')],
        	['value' => 'floor', 'label' => __('Rounding Down')],
        	['value' => 'ceil', 'label' => __('Rounding Up')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
			'round' => __('Normal'),
			'floor' => __('Rounding Down'),
			'ceil'  => __('Rounding Up')
        ];
    }
}