<?php
namespace Mitac\Theme\Model;
use Mitac\Theme\Api\Data\ColorInterface;
use Magento\Framework\Model\AbstractModel;

class Color extends AbstractModel implements ColorInterface
{
    protected function _construct()
    {
        $this->_init(\Mitac\Theme\Model\ResourceModel\Color::class);
    }

    public function create(): ColorInterface
    {
        return $this;
    }

    public function getColorId()
    {
        return $this->getData('color_id');
    }

    public function setColorId($colorId)
    {
        return $this->setData('color_id', $colorId);
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    public function getEnabled()
    {
        return $this->getData('enabled');
    }

    public function setEnabled($enabled)
    {
        return $this->setData('enabled', $enabled);
    }

    public function getStoreIds()
    {
        return $this->getData('store_ids');
    }

    public function setStoreIds($storeIds)
    {
        return $this->setData('store_ids', $storeIds);
    }

    public function getCategoryPage()
    {
        return $this->getData('category_page');
    }

    public function setCategoryPage($categoryPage)
    {
        return $this->setData('category_page', $categoryPage);
    }

    public function getProductPage()
    {
        return $this->getData('product_page');
    }

    public function setProductPage($productPage)
    {
        return $this->setData('product_page', $productPage);
    }

    public function getCmsPage()
    {
        return $this->getData('cms_page');
    }

    public function setCmsPage($cmsPage)
    {
        return $this->setData('cms_page', $cmsPage);
    }

    public function getBlogCategoryPage()
    {
        return $this->getData('blog_category_page');
    }

    public function setBlogCategoryPage($blogCategoryPage)
    {
        return $this->setData('blog_category_page', $blogCategoryPage);
    }

    public function getBlogPage()
    {
        return $this->getData('blog_page');
    }

    public function setBlogPage($blogPage)
    {
        return $this->setData('blog_page', $blogPage);
    }

    public function getColorAttrName()
    {
        return $this->getData('color_attr_name');
    }

    public function setColorAttrName($colorAttrName)
    {
        return $this->setData('color_attr_name', $colorAttrName);
    }

    public function getColor()
    {
        return $this->getData('color');
    }

    public function setColor($color)
    {
        return $this->setData('color', $color);
    }

    public function getCustomUrl()
    {
        return $this->getData('custom_url');
    }

    public function setCustomUrl($customUrl)
    {
        return $this->setData('custom_url', $customUrl);
    }

    public function getFaviconUrl()
    {
        return $this->getData('favicon_url');
    }

    public function setFaviconUrl($faviconUrl)
    {
        return $this->setData('favicon_url', $faviconUrl);
    }
}
