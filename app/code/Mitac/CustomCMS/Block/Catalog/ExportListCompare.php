<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mitac\CustomCMS\Block\Catalog;

class ExportListCompare extends \Magento\Framework\View\Element\Template
{

    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\CustomerData\CompareProducts $compareProducts,
		array $data = []
	) 
	{
		parent::__construct($context, $data);
        $this->compareProducts = $compareProducts;
	}

    public function getCompareList(){
        return $this->compareProducts->getSectionData();
    }
}
