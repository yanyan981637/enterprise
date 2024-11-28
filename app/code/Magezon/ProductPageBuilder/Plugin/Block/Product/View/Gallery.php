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

namespace Magezon\ProductPageBuilder\Plugin\Block\Product\View;

class Gallery
{
    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param callable $proceed
     * @param $name
     * @param $module
     * @return mixed
     */
    public function aroundGetVar(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        callable $proceed,
        $name,
        $module = null
    ) {
        if ($element = $subject->getProductPageBuilderElement()) {
            if (!$element->getUseDefaultThemeSettings()) {
                $path = str_replace('/', '_', $name);
                if ($element->hasData($path)) {
                    return $element->getData($path);
                }
            }
        }

        $result = $proceed($name, $module);

        return $result;
    }
}
