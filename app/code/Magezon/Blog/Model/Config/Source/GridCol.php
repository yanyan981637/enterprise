<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class GridCol implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => __('1 column'),
            'value' => 1
        ];
        $options[] = [
            'label' => __('2 columns'),
            'value' => 2
        ];
        $options[] = [
            'label' => __('3 columns'),
            'value' => 3
        ];
        $options[] = [
            'label' => __('4 columns'),
            'value' => 4
        ];
        $options[] = [
            'label' => __('5 columns'),
            'value' => 5
        ];
        $options[] = [
            'label' => __('6 columns'),
            'value' => 6
        ];
        return $options;
    }
}
