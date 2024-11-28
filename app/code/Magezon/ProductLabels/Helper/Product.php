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

namespace Magezon\ProductLabels\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magezon\ProductLabels\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $productOutput;

    /**
     * @param \Magento\Framework\App\Helper\Context                $context        
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager   
     * @param \Magento\Cms\Model\Template\FilterProvider           $filterProvider 
     * @param PriceCurrencyInterface                               $priceCurrency  
     * @param \Magento\Framework\Stdlib\DateTime\DateTime          $date           
     * @param \Magezon\ProductLabels\Helper\Data                   $helperData     
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry  
     * @param \Magento\Catalog\Helper\Output                       $productOutput  
     */
    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Cms\Model\Template\FilterProvider $filterProvider,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magezon\ProductLabels\Helper\Data $helperData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Helper\Output $productOutput
    	) {
        parent::__construct($context);
        $this->date            = $date;
        $this->_storeManager   = $storeManager;
        $this->_filterProvider = $filterProvider;
        $this->priceCurrency   = $priceCurrency;
        $this->helperData      = $helperData;
        $this->stockRegistry   = $stockRegistry;
        $this->productOutput   = $productOutput;
    }

    public function filter($product, $html)
    {
        $now = $this->date->date('Y-m-d 00:00:00');
        $regularPriceModel = $product->getPriceInfo()->getPrice('regular_price');
        $finalPriceModel   = $product->getPriceInfo()->getPrice('final_price');
        $regularPrice      = $regularPriceModel->getAmount()->getValue();
        $specialPrice      = $finalPriceModel->getAmount()->getValue();
        
        if ($product->getTypeId() == 'bundle') {
            $regularPrice = $regularPriceModel->getMinimalPrice()->getValue();
            $specialPrice = $finalPriceModel->getMinimalPrice()->getValue();
        }

		if (strpos($html, '{PRICE}') !== false) {
			$html = str_replace('{PRICE}', $this->formatPrice($regularPrice), $html);
        }

        if (strpos($html, '{SKU}') !== false) {
			$html = str_replace('{SKU}', $product->getSku(), $html);
		}

		if (strpos($html, '{SPECIAL_PRICE}') !== false) {
            $value =  $this->formatPrice($specialPrice);
            if ($product->getSpecialFromDate() && $now >= $product->getSpecialFromDate()) {
                if ($product->getSpecialToDate()) {
                    if ($now > $product->getSpecialToDate()) {
                        $value = '';
                    }
                }
            }
            if ($regularPrice == $specialPrice) {
                $value = '';
            }
			$html = str_replace('{SPECIAL_PRICE}', $value, $html);
		}

        if (strpos($html, '{SAVE_AMOUNT}') !== false) {
            $value = ($regularPrice - $specialPrice);
            if ($value) {
                $value = $this->formatPrice($value);
            } else {
                $value = '';
            }
            $html = str_replace('{SAVE_AMOUNT}', $value, $html);
        }

        if (strpos($html, '{SAVE_PERCENT}') !== false) {
            $roundingMethod = $this->helperData->getConfig('general/rounding_method');
            //$percent        = ($regularPrice - $specialPrice) * 100 / $regularPrice;
            if ($regularPrice > 0 && $specialPrice > 0) {
                $percent = ($regularPrice - $specialPrice) * 100 / $regularPrice;
            } else {
                $percent = 0;
            }
            switch ($roundingMethod) {
                case 'round':
                    $percent = round($percent);
                    break;

                case 'floor':
                    $percent = floor($percent);
                    break;

                case 'ceil':
                    $percent = ceil($percent);
                    break;
            }
            //if ($percent == 0) { $percent = ''; }
            $html = str_replace('{SAVE_PERCENT}', $percent, $html);
        }

        if (strpos($html, '{SPECIAL_DAY}') !== false) {
            $value = '';
            $toDate = $product->getSpecialToDate();
            if ($product->getTypeId() == 'configurable') {
                $usedProducts = $product->getData('productlabels_usedproducts');
                if ($usedProducts && $usedProducts->count()) {
                    foreach ($usedProducts as $_product) {
                        if ($_product->getSpecialFromDate() && $now >= $_product->getSpecialFromDate() && $_product->getSpecialPrice() == $specialPrice) {
                            $toDate = $_product->getSpecialToDate();
                            if ($_product->getSpecialToDate()) {
                                if ($now > $_product->getSpecialToDate()) {
                                    $toDate = '';
                                }
                            }
                        }
                    }
                }
            }
            if ($toDate) {
                $currentTime = $this->date->date();
                $diff        = strtotime($toDate) - strtotime($currentTime);
                if ($diff >= 0) {
                    $value = floor($diff / (60*60*24));
                }
            }
            $html = str_replace('{SPECIAL_DAY}', $value, $html);
        }

        if (strpos($html, '{SPECIAL_HOUR}') !== false) {
            $value = '';
            $toDate = $product->getSpecialToDate();
            if ($product->getTypeId() == 'configurable') {
                $usedProducts = $product->getData('productlabels_usedproducts');
                if ($usedProducts && $usedProducts->count()) {
                    foreach ($usedProducts as $_product) {
                        if ($_product->getSpecialFromDate() && $now >= $_product->getSpecialFromDate() && $_product->getSpecialPrice() == $specialPrice) {
                            $toDate = $_product->getSpecialToDate();
                            if ($_product->getSpecialToDate()) {
                                if ($now > $_product->getSpecialToDate()) {
                                    $toDate = '';
                                }
                            }
                        }
                    }
                }
            }
            if ($toDate) {
                $currentTime = $this->date->date();
                $diff        = strtotime($toDate) - strtotime($currentTime);
                if ($diff >= 0) {
                    $value = floor($diff / (60*60));
                }
            }
            $html = str_replace('{SPECIAL_HOUR}', $value, $html);
        }

        if (strpos($html, '{NEW_FOR}') !== false) {
            $value = '';
            if ($product->getNewsFromDate() && $now >= $product->getNewsFromDate()) {
                $newFromDate = strtotime($product->getNewsFromDate());
                $value     = max(1, floor((time() - $newFromDate) / 86400));
                if ($product->getNewsToDate()) {
                    if ($now > $product->getNewsToDate()) {
                        $value = '';
                    }
                }
            }
            $html = str_replace('{NEW_FOR}', $value, $html);
        }

        if (strpos($html, '{QTY}') !== false) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $html      = str_replace('{QTY}', $stockItem->getQty(), $html);
        }

        if (strpos($html, '{ATTR:') !== false) {
            $value = '';
            $count = substr_count($html, "{ATTR:");
            $firstPosition = 0;
            for ($i=0; $i < $count; $i++) {
                if($firstPosition==0) $tmp = $firstPosition;
                $firstPosition = strpos($html, "{ATTR:", $tmp)+6;
                $nextPosition  = strpos($html, "}", $firstPosition);
                $tmp           = $firstPosition;
                $length        = $nextPosition - $firstPosition;
                $code          = substr($html, $firstPosition, $length);
                $_attr         = $this->getAdditionalData($product, $code);
                if (!empty($_attr)) {
                    $value = $this->productOutput->productAttribute($product, $_attr['value'], $_attr['code']);
                }
                $html  = str_replace("{ATTR:" . $code . "}", $value, $html);
            }
        }

		$storeId = $this->_storeManager->getStore()->getId();
		return $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($html);
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @param boolean $format             Format price to currency format
     * @param boolean $includeContainer   Enclose into <span class="price"><span>
     * @return float
     */
    public function formatPrice($price, $format = true, $includeContainer = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat($price, $includeContainer)
            : $this->priceCurrency->convert($price);
    }

    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAdditionalData($product, $code, array $excludeAttr = [])
    {
        $data = [];
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($code == $attribute->getAttributeCode()) {
                $val = [];
                if (!in_array($attribute->getAttributeCode(), $excludeAttr)) {
                    $value = $attribute->getFrontend()->getValue($product);
                    if (!$product->hasData($attribute->getAttributeCode())) {
                        $value = __('N/A');
                    } elseif ((string)$value == '') {
                        $value = __('No');
                    } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                        $value = $this->priceCurrency->convertAndFormat($value);
                    }
                    if (($value instanceof Phrase || is_string($value)) && strlen($value)) {
                        $val = [
                            'label' => __($attribute->getStoreLabel()),
                            'value' => $value,
                            'code'  => $attribute->getAttributeCode()
                        ];
                    }
                }
                return $val;
            }
        }
    }
}