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
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Block\Product\Element;

class PriceTier extends \Magezon\ProductPageBuilder\Block\Product\Element
{
    /**
     * @return boolean
     */
    public function isEnabled()
    {
        $product = $this->getProduct();
        if (!$product->getTierPrices()) return false;
        return parent::isEnabled();
    }
}