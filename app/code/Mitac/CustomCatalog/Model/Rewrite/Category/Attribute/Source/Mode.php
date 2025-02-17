<?php
namespace Mitac\CustomCatalog\Model\Rewrite\Category\Attribute\Source;

class Mode extends \Magento\Catalog\Model\Category\Attribute\Source\Mode
{

    public function getAllOptions()
    {
        if (!$this->_options) 
        {
            $this->_options = [
                ['value' => 'TYPE1' ,'label' => __('Type1:PRODUCTS')],
                ['value' => 'TYPE2' ,'label' => __('Type2:DEVICES_ACCESSORIES')],
                ['value' => 'TYPE3' ,'label' => __('Type3:DEVICES_MAPS')],
                ['value' => 'TYPE4' ,'label' => __('Type4:DEVICES_PREVIOUS')],
                ['value' => \Magento\Catalog\Model\Category::DM_PAGE, 'label' => __('Static block only')],
                ['value' => \Magento\Catalog\Model\Category::DM_MIXED, 'label' => __('Static block and products')],
                ['value' => 'TYPE7' ,'label' => __('Type7:MAP_WITH_FILTER')],
                ['value' => 'TYPE9' ,'label' => __('Type9: ＭioNext Listing Page')]
            ];
        }
        return $this->_options;
    }
}

    //     if (!$this->_options) {
    //         $this->_options = [
    //             ['value' => \Magento\Catalog\Model\Category::DM_PRODUCT, 'label' => __('Products only')],
    //             ['value' => \Magento\Catalog\Model\Category::DM_PAGE, 'label' => __('Static block only')],
    //             ['value' => \Magento\Catalog\Model\Category::DM_MIXED, 'label' => __('Static block and products')],
    //         ];
    //     }
    //     return $this->_options;