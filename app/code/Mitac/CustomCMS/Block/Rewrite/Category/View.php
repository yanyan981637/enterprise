<?php
namespace Mitac\CustomCMS\Block\Rewrite\Category;

class View extends \Magento\Catalog\Block\Category\View
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock(\Magento\Catalog\Block\Breadcrumbs::class);

        $category = $this->getCurrentCategory();
        if ($category)
        {
            $title = $category->getMetaTitle();
            if ($title) 
            {
                $this->pageConfig->getTitle()->set($title);
            }
            $description = $category->getMetaDescription();
            if ($description) 
            {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $category->getMetaKeywords();
            if ($keywords) 
            {
                $this->pageConfig->setKeywords($keywords);
            }
            if ($this->_categoryHelper->canUseCanonicalTag()) 
            {
                $this->pageConfig->addRemotePageAsset(
                    $category->getUrl(),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) 
            {
                $pageMainTitle->setPageTitle($this->getCurrentCategory()->getName());
            }
        }

        /* Depends on different type */
        switch($this->showDisplayMode())
        {
            case 'TYPE2': 
            case 'TYPE3':
            case 'TYPE7':
            case 'TYPE4':
            case 'TYPE9':
                $this->getLayout()->unsetElement('catalog.compare.sidebar');
                $this->getLayout()->unsetElement('catalog.leftnav');
                $this->getLayout()->unsetElement('m.catalog.horizontal');
                $this->getLayout()->unsetElement('m.catalog.navigation.horizontal.renderer');
            break;
        }

        return $this;
    }

    public function showDisplayMode()
    {
        return $this->getCurrentCategory()->getDisplayMode();
    }

    public function getProductType1()
    {
        return $this->getChildHtml('product_type1');
    }

    public function getProductType2()
    {
        return $this->getChildHtml('product_type2');
    }

    public function getProductType3()
    {
        return $this->getChildHtml('product_type3');
    }

    public function getProductType4()
    {
        return $this->getChildHtml('product_type4');
    }
    public function getProductType7()
    {
        return $this->getChildHtml('product_type7');
    }
    public function getProductType9()
    {
        return $this->getChildHtml('product_type9');
    }
}