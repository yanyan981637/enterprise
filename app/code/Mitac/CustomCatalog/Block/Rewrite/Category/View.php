<?php

namespace Mitac\CustomCatalog\Block\Rewrite\Category;

class View extends \Magento\Catalog\Block\Category\View
{
    public function checkCustomList(){
        switch($this->getCurrentCategory()->getDisplayMode()){
            case 'Custom1':
            case 'Custom2':
                return true;
            break;
        }
        return false;
    }

    public function getCustomProductListHtml()
    {
        switch($this->getCurrentCategory()->getDisplayMode()){
            case 'Custom1':
                return $this->getChildHtml('custom_list1');
            break;
            case 'Custom2':
                return $this->getChildHtml('custom_list2');
            break;
        }
    }
}