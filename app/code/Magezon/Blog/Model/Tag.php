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

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magezon\Blog\Api\Data\TagInterface;
use Magezon\Blog\Helper\Data;

class Tag extends AbstractModel implements TagInterface, IdentityInterface
{
    /**#@+
     * Tag's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Blog tag cache tag
     */
    const CACHE_TAG = 'blog_t';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_tag';

    /**
     * @var ResourceModel\Post\Collection
     */
    protected $postCollection;

    /**
     * @var FilterManager
     */
    protected $filter;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Data
     */
    protected $dataHelper;

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
        $this->filter = $filter;
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
        $this->_init(ResourceModel\Tag::class);
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
        return parent::getData(self::TAG_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return TagInterface
     */
    public function setId($id)
    {
        return $this->setData(self::TAG_ID, $id);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return parent::getData(self::IDENTIFIER);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return TagInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return TagInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent()
    {
        return parent::getData(self::CONTENT);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return TagInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle()
    {
        return parent::getData(self::META_TITLE);
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return TagInterface
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
        return parent::getData(self::META_KEYWORDS);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return TagInterface
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
        return parent::getData(self::META_DESCRIPTION);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return TagInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime()
    {
        return parent::getData(self::CREATION_TIME);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return TagInterface
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
        return parent::getData(self::UPDATE_TIME);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return TagInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive()
    {
        return parent::getData(self::IS_ACTIVE);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return TagInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
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
     * @return TagInterface
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        return $this->setData(self::CANONICAL_URL, $canonicalUrl);
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
     * @return array
     */
    public function getPostsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $list = [];
        $collection = $this->getPostCollection();
        foreach ($collection as $_post) {
            $list[$_post->getId()] = 0;
        }
        return $list;
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
     * @return string|void
     */
    public function getPath()
    {
        $dataHelper = $this->dataHelper;
        if (!$dataHelper->getConfig('tag_page/enabled')) {
            return;
        }

        $route = $dataHelper->getRoute();
        $identifier = $route . '/' . $dataHelper->getTagRoute() . '/';
        $identifier .= $this->getIdentifier() . $dataHelper->getTagUrlSuffix();
        return $identifier;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilder->getUrl(null, ['_direct' => $this->getPath()]);
    }
}
