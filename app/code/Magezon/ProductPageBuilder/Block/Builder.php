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

namespace Magezon\ProductPageBuilder\Block;

class Builder extends \Magezon\Builder\Block\Builder
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context          $context
     * @param \Magezon\ProductPageBuilder\Model\CompositeConfigProvider $configProvider
     * @param array                                                     $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magezon\ProductPageBuilder\Model\CompositeConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $configProvider, $data);
    }
}
