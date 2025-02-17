<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mitac\CustomCMS\Block\Home;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;

class Category extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;
    /**
    * @var \Magento\Catalog\Model\CategoryFactory
    */
    protected $categoryFactory;
    
    /**
     * @var Render
     */    
    protected $priceRender;

    public function __construct(
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        Render $priceRender,
		array $data = []
	) 
	{
		parent::__construct($context, $data);
        $this->imageBuilder = $imageBuilder;
        $this->categoryFactory = $categoryFactory;
        $this->priceRender = $priceRender;
	}

    public function getCategoryIdByName($categoryTitle){
        $collection = $this->categoryFactory->create()->getCollection()->addFieldToFilter('name', ['in' => $categoryTitle]);
        $categoryId = null;
        if ($collection->getSize()) {
            $categoryId = $collection->getFirstItem()->getId();
        }
        return $categoryId;
    }

    public function getAllCategoryProductsById($id){
        $allcategoryproduct = $this->categoryFactory->create()->load($id)->getProductCollection();
        return $allcategoryproduct;
    }

    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

    protected function getPriceRender()
    {   
        return $this->priceRender->setData('is_product_list', true);
    }

    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
            FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

}
