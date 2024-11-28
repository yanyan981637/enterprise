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

class DateFormat implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $formats = [
            'F j, Y',
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'F j, Y g:i a',
            'F j, Y g:i A',
            'Y-m-d g:i a',
            'Y-m-d g:i A',
            'd/m/Y g:i a',
            'd/m/Y g:i A',
            'm/d/Y H:i',
            'd/m/Y H:i'
        ];
        foreach ($formats as $_type) {
            $options[] = [
                'value' => $_type,
                'label' => $_type . ' (' . date($_type, strtotime('now')) . ')'
            ];
        }
        return $options;
    }
}
