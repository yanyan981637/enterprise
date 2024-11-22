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

class ProductTypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('None')],
            ['value' => 'latest', 'label' => __('Latest')],
            ['value' => 'new', 'label' => __('New Arrival')],
            ['value' => 'bestseller', 'label' => __('Best Sellers')],
            ['value' => 'onsale', 'label' => __('On Sale')],
            ['value' => 'mostviewed', 'label' => __('Most Viewed')],
            ['value' => 'wishlisttop', 'label' => __('Wishlist Top')],
            ['value' => 'toprated', 'label' => __('Top Rated')],
            ['value' => 'featured', 'label' => __('Featured')],
            ['value' => 'free', 'label' => __('Free')]
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
            ''            => __('None'),
            'latest'      => __('Latest'),
            'new'         => __('New Arrival'),
            'bestseller'  => __('Best Sellers'),
            'onsale'      => __('Sale'),
            'mostviewed'  => __('Most Viewed'),
            'wishlisttop' => __('Wishlist Top'),
            'toprated'    => __('Top Rated'),
            'featured'    => __('Featured'),
            'free'        => __('Free')
        ];
    }
}