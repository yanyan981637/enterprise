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

namespace Magezon\ProductPageBuilder\Block\Product;

class Element extends \Magezon\Builder\Block\Element
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Framework\App\Http\Context               $httpContext
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry                       $registry
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext   = $httpContext;
        $this->priceCurrency = $priceCurrency;
        $this->coreRegistry  = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->getData('enable_cache')) {
            $this->addData([
                'cache_lifetime' => $this->getData('cache_lifetime'),
                'cache_tags' => [
                    \Magezon\ProductPageBuilder\Model\Profile::CACHE_TAG,
                    \Magento\Catalog\Model\Product::CACHE_TAG
                ]
            ]);
        }
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'PRODUCTPAGEBUILDER_ELEMENT',
            $this->priceCurrency->getCurrencySymbol(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->getElementType(),
            $this->getElementId(),
            $this->getTemplate(),
            $this->getProduct()->getId(),
            $this->getRequest()->getFullActionName()
        ];
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * @return \Magezon\ProductPageBuilder\Model\Profile
     */
    public function getProfile()
    {
        return $this->coreRegistry->registry('productpagebuilder_profile');
    }

    public function toHtml()
    {
        $product = $this->getProduct();
        if (!$product || !$product->getId()) {
            return;
        }
        return parent::toHtml();
    }
}
