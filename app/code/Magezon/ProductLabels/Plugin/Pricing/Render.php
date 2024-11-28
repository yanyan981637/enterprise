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

namespace Magezon\ProductLabels\Plugin\Pricing;

use Magento\Framework\Pricing\SaleableInterface;

class Render
{
    /**
     * @var \Magezon\ProductLabels\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Magezon\ProductLabels\Helper\Data $helperData
     */
    public function __construct(
        \Magezon\ProductLabels\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

	public function aroundRender(\Magento\Framework\Pricing\Render $subject, \Closure $proceed, $priceCode, SaleableInterface $saleableItem, array $arguments = []) {
        $html   = '';
        $result = $proceed($priceCode, $saleableItem, $arguments);
        if ($this->helperData->isEnable()) {
            $labels = (array) $saleableItem->getData('label_items');
            if (count($labels)>0) {
                foreach ($labels as $_label) {
                    $html .= $subject->getLayout()->createBlock('\Magento\Framework\View\Element\Template')
                    ->setProduct($saleableItem)
                    ->setProductLabel($_label)->setTemplate('Magezon_ProductLabels::label.phtml')->toHtml();
                }
            }
        }
		return $result . $html;
	}
}