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

class Sharing extends \Magezon\ProductPageBuilder\Block\Product\Element
{
    /**
     * @return boolean
     */
    public function isEnabled()
    {
    	$element = $this->getElement();
        if ($element->getEnableFacebookLike() || $element->getEnableFacebookShare() || $element->getEnableTwitter() || $element->getEnablePinterest()) {
            return parent::isEnabled();
        }
        return false;
    }
}