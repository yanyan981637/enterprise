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

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class RelatedProducts extends \Magezon\Builder\Block\ListProduct
{
    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * @param \Magento\Catalog\Block\Product\Context            $context
     * @param \Magento\Framework\App\Http\Context               $httpContext
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Url\Helper\Data                $urlHelper
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Catalog\Model\Product\Visibility         $catalogProductVisibility
     * @param \Magento\Catalog\Model\Config                     $catalogConfig
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $priceCurrency, $urlHelper, $data);
        $this->coreRegistry              = $registry;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig            = $catalogConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [
                \Magezon\ProductPageBuilder\Model\Profile::CACHE_TAG,
                \Magento\Catalog\Model\Product::CACHE_TAG
            ]
        ]);
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
            $this->getProduct()->getId()
        ];
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        if (!$this->getCollection()->count()) {
            return false;
        }
        return parent::isEnabled();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Get collection items
     *
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_itemCollection === null) {
            $product = $this->getProduct();
            /* @var $product \Magento\Catalog\Model\Product */

            $attributes = $this->_catalogConfig->getProductAttributes();
            $attributes[] = 'required_options';

            $this->_itemCollection = $product->getRelatedProductCollection()->addAttributeToSelect(
                $attributes
            )->setPositionOrder()->addStoreFilter();
            $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

            $this->_itemCollection->load();

            foreach ($this->_itemCollection as $product) {
                $product->setDoNotUseCategoryId(true);
            }
        }
        return $this->_itemCollection;
    }
    /**
     * @return string
     */
    public function getAdditionalStyleHtml()
    {
        $styleHtml  = '';
        $element    = $this->getElement();
        $useDefaultThemeLayout = $element->hasData('use_default_theme_layout');
        $useDefault = $useDefaultThemeLayout ? $useDefaultThemeLayout : true;
        if (!$useDefault) {
            $styleHtml = $this->getOwlCarouselStyles();
            $styleHtml .= $this->getLineStyles();
        }
        return $styleHtml;
    }
}
