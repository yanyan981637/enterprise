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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magezon\Blog\Api\Data\CategoryInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\ResourceModel\Post\Collection;

class Category extends AbstractModel implements CategoryInterface, IdentityInterface
{
    /**#@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Blog category cache tag
     */
    const CACHE_TAG = 'blog_c';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_category';

    /**
     * @var Collection
     */
    protected $filter;

    /**
     * @var Collection
     */
    protected $urlBuilder;

    /**
     * @var Collection
     */
    protected $dataHelper;

    /**
     * @var Collection
     */
    protected $postCollection;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FilterManager $filter
     * @param UrlInterface $urlBuilder
     * @param Data $dataHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FilterManager $filter,
        UrlInterface $urlBuilder,
        Data $dataHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->filter     = $filter;
        $this->urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Category::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return CategoryInterface
     */
    public function setId($id)
    {
        return $this->setData(self::CATEGORY_ID, $id);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return CategoryInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return CategoryInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return CategoryInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get parent id
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * Set parent id
     *
     * @param int $parentId
     * @return CategoryInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return CategoryInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get includeInMenu
     *
     * @return int|null
     */
    public function getIncludeInMenu()
    {
        return $this->getData(self::INCLUDE_IN_MENU);
    }

    /**
     * Set includeInMenu
     *
     * @param int $includeInMenu
     * @return CategoryInterface
     */
    public function setIncludeInMenu($includeInMenu)
    {
        return $this->setData(self::INCLUDE_IN_MENU, $includeInMenu);
    }

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return CategoryInterface
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return CategoryInterface
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return CategoryInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Set position
     *
     * @param int $position
     * @return CategoryInterface
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return CategoryInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return CategoryInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Get list layout
     *
     * @return string
     */
    public function getListLayout()
    {
        return $this->getData(self::LIST_LAYOUT);
    }

    /**
     * Set list layout
     *
     * @param string $listLayout
     * @return CategoryInterface
     */
    public function setListLayout($listLayout)
    {
        return $this->setData(self::LIST_LAYOUT, $listLayout);
    }

    /**
     * Get grid col
     *
     * @return int|null
     */
    public function getGridCol()
    {
        return $this->getData(self::GRID_COL);
    }

    /**
     * Set grid col
     *
     * @param int $gridCol
     * @return CategoryInterface
     */
    public function setGridCol($gridCol)
    {
        return $this->setData(self::GRID_COL, $gridCol);
    }

    /**
     * Get page layout
     *
     * @return string
     */
    public function getPageLayout()
    {
        return $this->getData(self::PAGE_LAYOUT) ? $this->getData(self::PAGE_LAYOUT) : '2columns-right';
    }

    /**
     * Set page layout
     *
     * @param string $pageLayout
     * @return CategoryInterface
     */
    public function setPageLayout($pageLayout)
    {
        return $this->setData(self::PAGE_LAYOUT, $pageLayout);
    }

    /**
     * Get canonical url
     *
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        return parent::getData(self::CANONICAL_URL);
    }

    /**
     * Set canonical url
     *
     * @param string $canonicalUrl
     * @return CategoryInterface
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        return $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @inheritDoc
     */
    public function setPostsSortBy($postsSortBy){
        return $this->setData(self::POSTS_SORT_BY, $postsSortBy);
    }

    /**
     * @inheritDoc
     */
    public function getPostsSortBy(){
        return parent::getData(self::POSTS_SORT_BY);
    }

    /**
     * Retrieve category posts
     *
     * @return Collection
     */
    public function getPostCollection()
    {
        if ($this->postCollection === null) {
            $this->postCollection = $this->_getResource()->getPostCollection($this);
        }
        return $this->postCollection;
    }
    
    /**
     * Retrieve array of posts id's for category
     *
     * The array returned has the following format:
     * array($postId => $position)
     *
     * @return array
     */
    public function getPostsPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('posts_position');
        if ($array === null) {
            $array = $this->_getResource()->getPostsPosition($this);
            $this->setData('posts_position', $array);
        }
        return $array;
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->filter->translitUrl($str);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $dataHelper = $this->dataHelper;
        $route      = $dataHelper->getRoute();
        $identifier = $route . '/';
        if ($dataHelper->getCategoryRoute()) $identifier .= $dataHelper->getCategoryRoute() . '/';
        $identifier .= $this->getIdentifier() . $dataHelper->getCategoryUrlSuffix();
        return $identifier;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilder->getUrl(null, ['_direct' => $this->getPath()]);
    }

    /**
     * Retrieve count posts of category
     *
     * @return int
     */
    public function getPostCount()
    {
        if (!$this->hasData('post_count')) {
            $count = $this->_getResource()->getPostCount($this);
            $this->setData('post_count', $count);
        }
        return $this->getData('post_count');
    }

    /**
     * @param int $count
     */
    public function setPostCount($count)
    {
        $this->setData('post_count', $count);
        return $this;
    }
}