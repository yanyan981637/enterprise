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
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Api\Data\PostInterface;
use Magezon\Blog\Helper\Image;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Core\Helper\Data;

class Post extends AbstractModel implements PostInterface, IdentityInterface
{
    /**#@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';

    const LAYOUT_FIXED_THUMBNAIL = 'fixed_thumb';
    const LAYOUT_FULL_THUMBNAIL = 'full_thumb';
    const LAYOUT_GRID = 'grid';
    const LAYOUT_MASONRY = 'masonry';

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'blog_p';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_post';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var ResourceModel\Tag\Collection
     */
    protected $tagCollection;

    /**
     * @var ResourceModel\Category\Collection
     */
    protected $categoryList;

    /**
     * @var Author
     */
    protected $_author;

    /**
     * @var Collection
     */
    protected $_relatedPostCollection;

    /**
     * @var FilterManager
     */
    protected $filter;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AuthorFactory
     */
    protected $_authorFactory;

    /**
     * @var Data
     */
    protected $coreHelper;

    /**
     * @var \Magezon\Blog\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var array
     */
    protected $nextAndPrevPost;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FilterManager $filter
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param FilterManager $filterManager
     * @param AuthorFactory $authorFactory
     * @param Data $coreHelper
     * @param \Magezon\Blog\Helper\Data $dataHelper
     * @param Image $imageHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FilterManager $filter,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        FilterManager $filterManager,
        AuthorFactory $authorFactory,
        Data $coreHelper,
        \Magezon\Blog\Helper\Data $dataHelper,
        Image $imageHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->filter = $filter;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->filterManager = $filterManager;
        $this->_authorFactory = $authorFactory;
        $this->coreHelper = $coreHelper;
        $this->dataHelper = $dataHelper;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Post::class);
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
        return parent::getData(self::POST_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return PostInterface
     */
    public function setId($id)
    {
        return $this->setData(self::POST_ID, $id);
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
     * @return PostInterface
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
     * @return PostInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get publish date
     *
     * @return string|null
     */
    public function getPublishDate()
    {
        if ($this->validateDate(parent::getData(self::PUBLISH_DATE))) {
            return parent::getData(self::PUBLISH_DATE);
        }

    }

    /**
     * Set publish date
     *
     * @param string $publishDate
     * @return PostInterface
     */
    public function setPublishDate($publishDate)
    {
        return $this->setData(self::PUBLISH_DATE, $publishDate);
    }

    /**
     * Get image
     *
     * @return string|null
     */
    public function getImage()
    {
        return parent::getData(self::IMAGE);
    }

    /**
     * Set image
     *
     * @param string $image
     * @return PostInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
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
     * @return PostInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getExcerpt()
    {
        return parent::getData(self::EXCERPT);
    }

    /**
     * Set excerpt
     *
     * @param string $excerpt
     * @return PostInterface
     */
    public function setExcerpt($excerpt)
    {
        return $this->setData(self::EXCERPT, $excerpt);
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
     * @return PostInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get author id
     *
     * @return int|null
     */
    public function getAuthorId()
    {
        return parent::getData(self::AUTHOR_ID);
    }

    /**
     * Set author id
     *
     * @param int $authorId
     * @return PostInterface
     */
    public function setAuthorId($authorId)
    {
        return $this->setData(self::AUTHOR_ID, $authorId);
    }

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PostInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get og title
     *
     * @return string|null
     */
    public function getOgTitle()
    {
        return parent::getData(self::OG_TITLE);
    }

    /**
     * Set og title
     *
     * @param string $ogTitle
     * @return PostInterface
     */
    public function setOgTitle($ogTitle)
    {
        return $this->setData(self::OG_TITLE, $ogTitle);
    }

    /**
     * Get og description
     *
     * @return string|null
     */
    public function getOgDescription()
    {
        return parent::getData(self::OG_DESCRIPTION);
    }

    /**
     * Set og description
     *
     * @param string $ogDescription
     * @return PostInterface
     */
    public function setOgDescription($ogDescription)
    {
        return $this->setData(self::OG_DESCRIPTION, $ogDescription);
    }

    /**
     * Get og img
     *
     * @return string|null
     */
    public function getOgImg()
    {
        return parent::getData(self::OG_IMG);
    }

    /**
     * Set og img
     *
     * @param string $ogImg
     * @return PostInterface
     */
    public function setOgImg($ogImg)
    {
        return $this->setData(self::OG_IMG, $ogImg);
    }

    /**
     * Get og type
     *
     * @return string|null
     */
    public function getOgType()
    {
        return parent::getData(self::OG_TYPE);
    }

    /**
     * Set og type
     *
     * @param string $ogType
     * @return PostInterface
     */
    public function setOgType($ogType)
    {
        return $this->setData(self::OG_TYPE, $ogType);
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
     * @return PostInterface
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
     * @return PostInterface
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
     * @return PostInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get video link
     *
     * @return string|null
     */
    public function getVideoLink()
    {
        return parent::getData(self::VIDEO_LINK);
    }

    /**
     * Set video link
     *
     * @param string $videoLink
     * @return PostInterface
     */
    public function setVideoLink($videoLink)
    {
        return $this->setData(self::VIDEO_LINK, $videoLink);
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
     * @return PostInterface
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
     * @return PostInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Get total views
     *
     * @return int|null
     */
    public function getTotalViews()
    {
        return parent::getData(self::TOTAL_VIEWS);
    }

    /**
     * Set total views
     *
     * @param int $totalViews
     * @return PostInterface
     */
    public function setTotalViews($totalViews)
    {
        return $this->setData(self::TOTAL_VIEWS, $totalViews);
    }

    /**
     * Is featured
     *
     * @return bool|null
     */
    public function isFeatured()
    {
        return parent::getData(self::FEATURED);
    }

    /**
     * Set is featured
     *
     * @param int|bool $featured
     * @return PostInterface
     */
    public function setFeatured($featured)
    {
        return $this->setData(self::FEATURED, $featured);
    }

    /**
     * Is pinned
     *
     * @return bool|null
     */
    public function isPinned()
    {
        return parent::getData(self::PINNED);
    }

    /**
     * Set is pinned
     *
     * @param int|bool $pinned
     * @return PostInterface
     */
    public function setPinned($pinned)
    {
        return $this->setData(self::PINNED, $pinned);
    }

    /**
     * Get allow comment
     *
     * @return int|null
     */
    public function getAllowComment()
    {
        if (!$this->dataHelper->getConfig('post_page/comments/type')) {
            return;
        }

        return parent::getData(self::ALLOW_COMMENT);
    }

    /**
     * Set allow comment
     *
     * @param int $allowComment
     * @return PostInterface
     */
    public function setAllowComment($allowComment)
    {
        return $this->setData(self::ALLOW_COMMENT, $allowComment);
    }

    /**
     * Get page layout
     *
     * @return string|null
     */
    public function getPageLayout()
    {
        return parent::getData(self::PAGE_LAYOUT) ? parent::getData(self::PAGE_LAYOUT) : '2columns-right';
    }

    /**
     * Set page layout
     *
     * @param string $pageLayout
     * @return PostInterface
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
     * @return PostInterface
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        return $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @inheritDoc
     */
    public function getLikeTotal(){
        return parent::getData(self::LIKE_TOTAL);
    }

    /**
     * @inheritDoc
     */
    public function setLikeTotal($likeTotal){
        return $this->setData(self::LIKE_TOTAL, $likeTotal);
    }

    /**
     * @inheritDoc
     */
    public function getDislikeTotal(){
        return parent::getData(self::DISLIKE_TOTAL);
    }

    /**
     * @inheritDoc
     */
    public function setDislikeTotal($dislikeTotal){
        return $this->setData(self::DISLIKE_TOTAL, $dislikeTotal);
    }

    /**
     * @inheritDoc
     */
    public function getReadTime(){
        return parent::getData(self::READ_TIME);
    }

    /**
     * @inheritDoc
     */
    public function setReadTime($readTime){
        return $this->setData(self::READ_TIME, $readTime);
    }

    /**
     * @inheritDoc
     */
    public function setEndDate($endDate){
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * @inheritDoc
     */
    public function getEndDate(){
        return parent::getData(self::END_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setPosition($position){
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(){
        return parent::getData(self::POSITION);
    }


    /**
     * Retrieve post products
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        if ($this->productCollection === null) {
            $this->productCollection = $this->_getResource()->getProductCollection($this);
        }
        return $this->productCollection;
    }

    /**
     * Retrieve post tags
     *
     * @return ResourceModel\Tag\Collection
     */
    public function getTagCollection()
    {
        if ($this->tagCollection === null) {
            $this->tagCollection = $this->_getResource()->getTagCollection($this);
        }
        return $this->tagCollection;
    }

    /**
     * @return ResourceModel\Category\Collection
     * @throws LocalizedException
     */
    public function getCategoryList()
    {
        if ($this->categoryList === null) {
            $this->categoryList = $this->_getResource()->getCategoryList($this);
        }
        return $this->categoryList;
    }

    /**
     * @param array $categoryList
     */
    public function setCategoryList(array $categoryList)
    {
        $this->categoryList = $categoryList;
        return $this;
    }

    /**
     * @return Author
     */
    public function getAuthor()
    {
        if ($this->_author === null && $this->getAuthorId()) {
            $author = $this->_authorFactory->create();
            $author->load($this->getAuthorId());
            if ($author->isActive()) {
                $this->_author = $author;
            } else {
                $this->_author = false;
            }
        }
        return $this->_author;
    }

    /**
     * @param Author $author
     */
    public function setAuthor(Author $author)
    {
        $this->_author = $author;
        return $this;
    }

    /**
     * Retrieve assigned category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this->getId());
            $this->setData('category_ids', $ids);
        }
        return (array) $this->_getData('category_ids');
    }

    /**
     * Retrieve assigned tag Ids
     *
     * @return array
     */
    public function getTagIds()
    {
        if (!$this->hasData('tag_ids')) {
            $ids = $this->_getResource()->getTagIds($this->getId());
            $this->setData('tag_ids', $ids);
        }

        return (array) $this->_getData('tag_ids');
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
     * @return array|mixed|null
     * @throws LocalizedException
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('products_position');
        if ($array === null) {
            $array = $this->_getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }

    /**
     * @return array|mixed|null
     * @throws LocalizedException
     */
    public function getPostedProductsModel()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('posted_products');
        if ($array === null) {
            $array = $this->_getResource()->getProductsPosition($this);
            $this->setData('posted_products', $array);
        }
        return $array;
    }

    /**
     * @param $date
     * @param $format
     * @return bool
     */
    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * @param  boolean $shortMonth
     * @return string
     */
    public function getCreatedAtFormatted($shortMonth = false)
    {
        $dateFormat = $this->dataHelper->getDateFormat();
        if ($shortMonth) {
            $dateFormat = str_replace('F', 'M', $dateFormat);
        }

        $date = $this->getCreationTime();
        if ($this->getPublishDate()) {
            $date = $this->getPublishDate();
        }

        return date($dateFormat, strtotime($date));
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $dataHelper = $this->dataHelper;
        $route = $dataHelper->getRoute();
        $identifier = $route . '/';
        if ($dataHelper->getPostUseCategories() && ($category = $this->getCategory())) {
            $identifier .= $category->getIdentifier() . '/';
        }
        $identifier .= $this->getIdentifier() . $dataHelper->getPostUrlSuffix();
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
     * @param  int $width
     * @param  int $height
     * @return string
     */
    public function getImageUrl($width = null, $height = null)
    {
        $image = $this->getData('image');
        if ($image) {
            if ($width && $height) {
                return $this->imageHelper->resize($image, $width, $height, 100, 'magezon/resized',
                    ['keepAspectRatio' => false, 'keepFrame' => false]
                );
            }
            return $this->getMediaUrl() . $image;
        }
    }

    /**
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return [
            self::TYPE_IMAGE => __('Image'),
            self::TYPE_VIDEO => __('Video'),
        ];
    }

    /**
     * @return array
     */
    public function getLayouts()
    {
        return [
            self::LAYOUT_FIXED_THUMBNAIL => __('List - Fixed Thumbnail'),
            self::LAYOUT_FULL_THUMBNAIL => __('List - Full Thumbnail'),
            self::LAYOUT_GRID => __('Grid'),
            self::LAYOUT_MASONRY => __('Masonry'),
        ];
    }

    /**
     * @return string
     */
    public function getPostExcerpt()
    {
        if ($this->getExcerpt()) {
            $excerpt = $this->coreHelper->filter($this->getExcerpt());
        } else {
            $excerpt = $this->_stripTags($this->getContent());
            $excerptLimit = $this->coreHelper->substr(strip_tags($excerpt), 300);
            $excerptCompare = $this->coreHelper->substr(strip_tags($excerpt), 350);
            if(strlen($excerptCompare) > strlen($excerptLimit)) {
                $excerptLimit = substr($excerptLimit, 0, strrpos($excerptLimit, " "));
                $excerpt = $excerptLimit . ' ...';
            }
        }
        return trim($excerpt);
    }

    private function _stripTags($string = '', $allowableTags = '<p> <b>', $allowHtmlEntities = null)
    {
        $string = $this->coreHelper->filter((string) $string);
        $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        $string = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $string);
        $string = $this->filterManager->stripTags(
            $string,
            ['allowableTags' => $allowableTags, 'escape' => $allowHtmlEntities]
        );
        return $string;
    }

    /**
     * @return string
     */
    public function getMetaCommentUrl()
    {
        $url = $this->getUrl();
        $commentType = $this->dataHelper->getCommentType();
        if ($commentType == Comment::TYPE_NATIVE) {
            $url .= ($this->getTotalComments() ? '#blog-post-comments' : '#respond');
        }
        if ($commentType == Comment::TYPE_DISQUS) {
            $url .= '#disqus_thread';
        }
        return $url;
    }

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        if (($categories = $this->getCategoryList()) && !empty($categories)) {
            return $categories[0];
        }
    }

    /**
     * @return string
     */
    public function getOgImageUrl()
    {
        $image = $this->getData('og_img');
        if ($image) {
            return $this->getMediaUrl() . $image;
        }

        return $this->getImageUrl();
    }

    /**
     * Retrieve array of posts id's for post
     *
     * The array returned has the following format:
     * array($productId => $position)
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
     * @return Collection
     */
    public function getRelatedPostCollection()
    {
        if ($this->_relatedPostCollection === null) {
            $this->_relatedPostCollection = $this->_getResource()->getRelatedPostCollection($this);
        }
        return $this->_relatedPostCollection;
    }

    /**
     * @param $optionValue
     * @return mixed
     * @throws LocalizedException
     */
    public function getNextAndPrevPost($optionValue)
    {
        if ($this->nextAndPrevPost === null) {
            $this->nextAndPrevPost = $this->_getResource()->getNextAndPrevPost($this, $optionValue);
        }
        return $this->nextAndPrevPost;
    }

    /**
     * @return array|mixed|null
     * @throws LocalizedException
     */
    public function getPostedPostsModel()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('post_posts');
        if ($array === null) {
            $array = $this->_getResource()->getPostsPosition($this);
            $this->setData('post_posts', $array);
        }
        return $array;
    }

    /**
     * @param array $categories
     * @return mixed
     * @throws LocalizedException
     */
    public function issetCategorires($categories)
    {
        return $this->_getResource()->issetCategorires($categories);
    }

    /**
     * @param array $tags
     * @return mixed
     * @throws LocalizedException
     */
    public function issetTags($tags)
    {
        return $this->_getResource()->issetTags($tags);
    }

    /**
     * @param $author
     * @return mixed
     * @throws LocalizedException
     */
    public function issetAuthor($author)
    {
        return $this->_getResource()->issetAuthor($author);
    }
}
