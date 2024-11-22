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

use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Attribute extends \Magezon\ProductPageBuilder\Block\Product\Element
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @var string
     */
    protected $_attributeLabel;

    /**
     * @var string
     */
    protected $_attributeValue;

    /**
     * @param \Magento\Framework\View\Element\Template\Context     $context       
     * @param \Magento\Framework\App\Http\Context                  $httpContext   
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency 
     * @param \Magento\Framework\Registry                          $registry      
     * @param \Magento\Catalog\Helper\Image                        $imageHelper   
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry 
     * @param \Magezon\Core\Helper\Data                            $coreHelper    
     * @param array                                                $data          
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magezon\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $priceCurrency, $registry, $data);
        $this->imageHelper   = $imageHelper;
        $this->stockRegistry = $stockRegistry;
        $this->coreHelper    = $coreHelper;
    }

    /**
     * @return string
     */
    public function getAttributeValue()
    {
        if ($this->_attributeValue == NULL) {
            $attributeCode = $this->getElement()->getAttribute();
            if (!$attributeCode) return;
            $product      = $this->getProduct();
            $typeInstance = $product->getTypeInstance();
            $attribute    = '';
            foreach ($typeInstance->getSetAttributes($product) as $_attribute) {
                if ($_attribute->getAttributeCode() == $attributeCode) {
                    $attribute = $_attribute;
                    break;
                }
            }
            if (!$attribute) return;

            $this->_attributeLabel = $attribute->getStoreLabel();

            if ($attributeCode == 'quantity_and_stock_status') {
                $value = $this->getStockQtyLeft();
            } else {
                $value = $attribute->getFrontend()->getValue($product);
                if ($value instanceof \Magento\Framework\Phrase) {
                    $value = (string)$value;
                } else if ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                } else if ($attribute->getFrontendInput() == 'media_image' && is_string($value)) {
                    if ($value==='no_selection') {
                        $value = '';
                    } else {
                        $productImage = $this->imageHelper->init($product, 'icon_image')->setImageFile($value);
                        $url          = $productImage->getUrl();
                        if ($url) {
                            $value = '<img src="' . $productImage->getUrl() .'"/>';
                        } else {
                            $value = '';
                        }
                    }
                }
                $value = $this->coreHelper->filter($value);
            }
            $this->_attributeValue = $value;
        }
        return $this->_attributeValue;
    }

    /**
     * @return string
     */
    public function getAttributeLabel()
    {
        return $this->_attributeLabel;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        $element = $this->getElement();
        if (!$element->getAttribute() || $this->getAttributeValue() == '') {
            return false;
        }
        return parent::isEnabled();
    }

    /**
     * Retrieve current product qty left in stock
     *
     * @return float
     */
    public function getStockQtyLeft()
    {
        $qty         = $this->getProductStockQty($this->getProduct());
        $stockItem   = $this->stockRegistry->getStockItem($this->getProduct()->getId());
        $minStockQty = $stockItem->getMinQty();
        return $qty - $minStockQty;
    }

    /**
     * Retrieve product stock qty
     *
     * @param Product $product
     * @return float
     */
    public function getProductStockQty($product)
    {
        return $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }
}