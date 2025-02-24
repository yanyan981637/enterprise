<?php
namespace Mitac\Theme\Api\Data;

interface ColorInterface
{
    const COLOR_ID = 'color_id';
    const ENABLED = 'enabled';
    const NAME = 'name';
    const STORE_IDS = 'store_ids';
    const CATEGORY_PAGE = 'category_page';
    const PRODUCT_PAGE = 'product_page';
    const CMS_PAGE = 'cms_page';
    const BLOG_CATEGORY_PAGE = 'blog_category_page';
    const BLOG_PAGE = 'blog_page';
    const COLOR_ATTR_NAME = 'color_attr_name';
    const COLOR = 'color';
    const CUSTOM_URL = 'custom_url';
    const FAVICON_URL = 'favicon_url';

    public function create();

    /**
     * @return int|null
     */
    public function getColorId();

    /**
     * @param int $colorId
     * @return $this
     */
    public function setColorId($colorId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @params string
     * @return $this
     */
    public function setName($name);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled($enabled);

    /**
     * @return string[]|null
     */
    public function getStoreIds();

    /**
     * @param string[] $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds);

    /**
     * @return bool|null
     */
    public function getCategoryPage();

    /**
     * @param bool $categoryPage
     * @return $this
     */
    public function setCategoryPage($categoryPage);

    /**
     * @return bool|null
     */
    public function getProductPage();

    /**
     * @param bool $productPage
     * @return $this
     */
    public function setProductPage($productPage);

    /**
     * @return bool|null
     */
    public function getCmsPage();

    /**
     * @param bool $cmsPage
     * @return $this
     */
    public function setCmsPage($cmsPage);

    /**
     * @return bool|null
     */
    public function getBlogCategoryPage();

    /**
     * @param bool $blogCategoryPage
     * @return $this
     */
    public function setBlogCategoryPage($blogCategoryPage);

    /**
     * @return bool|null
     */
    public function getBlogPage();

    /**
     * @param bool $blogPage
     * @return $this
     */
    public function setBlogPage($blogPage);

    /**
     * @return string|null
     */
    public function getColorAttrName();

    /**
     * @param string $colorAttrName
     * @return $this
     */
    public function setColorAttrName($colorAttrName);

    /**
     * @return string|null
     */
    public function getColor();

    /**
     * @param string $color
     * @return $this
     */
    public function setColor($color);

    /**
     * @return string|null
     */
    public function getCustomUrl();

    /**
     * @param string $customUrl
     * @return $this
     */
    public function setCustomUrl($customUrl);

    /**
     * @return string|null
     */
    public function getFaviconUrl();

    /**
     * @param string $faviconUrl
     * @return $this
     */
    public function setFaviconUrl($faviconUrl);
}
