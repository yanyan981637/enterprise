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

namespace Magezon\ProductPageBuilder\Plugin\View\Layout;

class Builder
{
    /**
     * @var boolean
     */
    protected $_valid;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magezon\ProductPageBuilder\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Registry             $registry
     * @param \Magezon\ProductPageBuilder\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Registry $registry,
        \Magezon\ProductPageBuilder\Helper\Data $dataHelper
    ) {
        $this->layout     = $layout;
        $this->registry   = $registry;
        $this->dataHelper = $dataHelper;
    }

    public function beforeBuild(
        $subject
    ) {
        if ($this->dataHelper->isEnable() &&
            $this->registry->registry('productpagebuilder_profile') &&
            !$this->_valid
        ) {
            $product = $this->registry->registry('product');
            $update = $this->layout->getUpdate();
            $update->addHandle('productpagebuilder');
            $update->addHandle('productpagebuilder_type_' . $product->getTypeId());
            $update->addHandle('productpagebuilder_id_' . $product->getTypeId());
            $update->addHandle('productpagebuilder_sku_' . $product->getSku());
            $this->_valid = true;
        }
    }
}
