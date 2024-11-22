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

namespace Magezon\Blog\Model\Import\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TypeList implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => 'Aheadworks',
            'value' => 'aheadworks'
        ];
        $options[] = [
            'label' => 'Amasty',
            'value' => 'amasty'
        ];
        $options[] = [
            'label' => 'MageFan',
            'value' => 'magefan'
        ];
        $options[] = [
            'label' => 'Mageplaza',
            'value' => 'mageplaza'
        ];
        $options[] = [
            'label' => 'Wordpress',
            'value' => 'wordpress'
        ];
        return $options;
    }
}
