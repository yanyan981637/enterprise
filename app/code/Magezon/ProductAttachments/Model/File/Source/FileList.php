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
 * @package   Magezon_FAQ
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\ProductAttachments\Model\File\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FileList implements OptionSourceInterface
{
    /**
     * @param bool $addEmptyField
     * @return array|array[]
     */
    public function toOptionArray($addEmptyField = true)
    {
        $options = [
            [
                'label' => 'Top Rated Download',
                'value' => 0
            ],
            [
                'label' => 'New Upload',
                'value' => 1
            ],
        ];
        return $options;
    }
}
