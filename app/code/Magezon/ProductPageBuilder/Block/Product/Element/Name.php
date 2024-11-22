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

class Name extends \Magezon\ProductPageBuilder\Block\Product\Element
{
    /**
     * @return string
     */
    public function getAdditionalStyleHtml()
    {
        $styleHtml = '';
        $element               = $this->getElement();
        $styles['color']       = $this->getStyleColor($element->getData('color'));
        $styles['font-size']   = $this->getStyleProperty($element->getData('font_size'));
        $styles['line-height'] = $this->getStyleProperty($element->getData('line_height'));
        $styles['font-weight'] = $element->getData('font_weight');
        $styleHtml .= $this->getStyles('.ppbd-product-name', $styles);

        return $styleHtml;
    }
}