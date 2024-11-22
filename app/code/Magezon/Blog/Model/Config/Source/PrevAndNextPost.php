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

/**
 * Class PostsSortBy Model
 */
class PrevAndNextPost implements ArrayInterface
{
    /**
     * @const int
     */
    const CATEGORY = 0;

    /**
     * @const int
     */
    const POST_ID = 1;

    /**
     * @const int
     */
    const AUTHOR = 2;

    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::CATEGORY, 'label' => __('Category (default)')],
            ['value' => self::POST_ID, 'label' => __('Publish Date')],
            ['value' => self::AUTHOR, 'label' => __('Author')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
