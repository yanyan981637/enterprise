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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Api\Data\AuthorInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class Author extends AbstractModel implements AuthorInterface, IdentityInterface
{
    const DISPLAY_F  = 'f'; // Firstname
    const DISPLAY_L  = 'l'; // Lastname
    const DISPLAY_FL = 'fl'; // Firstname Lastname
    const DISPLAY_LF = 'lf'; // Lastname Firstname
    const DISPLAY_N  = 'n'; // Nickname

    /**
     * Blog author cache tag
     */
    const CACHE_TAG = 'blog_a';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_author';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Post\Collection
     */
    protected $postCollection;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $_postCollectionFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $postCollectionFactory
     * @param Data $dataHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        CollectionFactory $postCollectionFactory,
        Data $dataHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->urlBuilder             = $urlBuilder;
        $this->storeManager           = $storeManager;
        $this->_postCollectionFactory = $postCollectionFactory;
        $this->dataHelper             = $dataHelper;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Author::class);
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
        return $this->getData(self::AUTHOR_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return AuthorInterface
     */
    public function setId($id)
    {
        return $this->setData(self::AUTHOR_ID, $id);
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
     * @return AuthorInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getData(self::FIRST_NAME);
    }

    /**
     * Set first name
     *
     * @param string $firstName
     * @return AuthorInterface
     */
    public function setFirstName($firstName)
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getData(self::LAST_NAME);
    }

    /**
     * Set last name
     *
     * @param string $lastName
     * @return AuthorInterface
     */
    public function setLastName($lastName)
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    /**
     * Get nick name
     *
     * @return string|null
     */
    public function getNickname()
    {
        return $this->getData(self::NICKNAME);
    }

    /**
     * Set nick name
     *
     * @param string $nickname
     * @return AuthorInterface
     */
    public function setNickname($nickname)
    {
        return $this->setData(self::NICKNAME, $nickname);
    }

    /**
     * Get display name
     *
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->getData(self::DISPLAY_NAME);
    }

    /**
     * Set display name
     *
     * @param string $displayName
     * @return AuthorInterface
     */
    public function setDisplayName($displayName)
    {
        return $this->setData(self::DISPLAY_NAME, $displayName);
    }

    /**
     * Get image
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Set image
     *
     * @param string $image
     * @return AuthorInterface
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
        return $this->getData(self::CONTENT);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return AuthorInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get short content
     *
     * @return string|null
     */
    public function getShortContent()
    {
        return $this->getData(self::SHORT_CONTENT);
    }

    /**
     * Set short content
     *
     * @param string $shortContent
     * @return AuthorInterface
     */
    public function setShortContent($shortContent)
    {
        return $this->setData(self::SHORT_CONTENT, $shortContent);
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
     * @return AuthorInterface
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
     * @return AuthorInterface
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
     * @return AuthorInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get twitter
     *
     * @return string|null
     */
    public function getTwitter()
    {
        return $this->getData(self::TWITTER);
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return AuthorInterface
     */
    public function setTwitter($twitter)
    {
        return $this->setData(self::TWITTER, $twitter);
    }

    /**
     * Get facebook
     *
     * @return string|null
     */
    public function getFacebook()
    {
        return $this->getData(self::FACEBOOK);
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return AuthorInterface
     */
    public function setFacebook($facebook)
    {
        return $this->setData(self::FACEBOOK, $facebook);
    }

    /**
     * Get linkedin
     *
     * @return string|null
     */
    public function getLinkedin()
    {
        return $this->getData(self::LINKEDIN);
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     * @return AuthorInterface
     */
    public function setLinkedin($linkedin)
    {
        return $this->setData(self::LINKEDIN, $linkedin);
    }

    /**
     * Get flickr
     *
     * @return string|null
     */
    public function getFlickr()
    {
        return $this->getData(self::FLICKR);
    }

    /**
     * Set flickr
     *
     * @param string $flickr
     * @return AuthorInterface
     */
    public function setFlickr($flickr)
    {
        return $this->setData(self::FLICKR, $flickr);
    }

    /**
     * Get youtube
     *
     * @return string|null
     */
    public function getYoutube()
    {
        return $this->getData(self::YOUTUBE);
    }

    /**
     * Set youtube
     *
     * @param string $youtube
     * @return AuthorInterface
     */
    public function setYoutube($youtube)
    {
        return $this->setData(self::YOUTUBE, $youtube);
    }

    /**
     * Get pinterest
     *
     * @return string|null
     */
    public function getPinterest()
    {
        return $this->getData(self::PINTEREST);
    }

    /**
     * Set pinterest
     *
     * @param string $pinterest
     * @return AuthorInterface
     */
    public function setPinterest($pinterest)
    {
        return $this->setData(self::PINTEREST, $pinterest);
    }

    /**
     * Get behance
     *
     * @return string|null
     */
    public function getBehance()
    {
        return $this->getData(self::BEHANCE);
    }

    /**
     * Set behance
     *
     * @param string $behance
     * @return AuthorInterface
     */
    public function setBehance($behance)
    {
        return $this->setData(self::BEHANCE, $behance);
    }

    /**
     * Get instagram
     *
     * @return string|null
     */
    public function getInstagram()
    {
        return $this->getData(self::INSTAGRAM);
    }

    /**
     * Set instagram
     *
     * @param string $instagram
     * @return AuthorInterface
     */
    public function setInstagram($instagram)
    {
        return $this->setData(self::INSTAGRAM, $instagram);
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
     * @return AuthorInterface
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
     * @return AuthorInterface
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
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return AuthorInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get user id
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * Set user id
     *
     * @param int $userId
     * @return AuthorInterface
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * @return array
     */
    public function getDisplayTypes()
    {
        return [
            self::DISPLAY_F  => __('Fistname'),
            self::DISPLAY_L  => __('Lastname'),
            self::DISPLAY_FL => __('Firstname - Lastname'),
            self::DISPLAY_LF => __('Lastname - Firstname'),
            self::DISPLAY_N  => __('Nickname')
        ];
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $name = $this->getFirstName();
        if ($name && $this->getLastName()) {
            $name .= ' ' . $this->getLastName();
        }
        return $name;
    }

    /**
     * @return string
     */
    public function getPublicName()
    {
        $name = '';

        switch ($this->getDisplayName()) {
            case self::DISPLAY_F:
            $name = $this->getFirstName();
            break;

            case self::DISPLAY_L:
            $name = $this->getLastName();
            break;

            case self::DISPLAY_FL:
            $name = $this->getFirstName();
            if ($this->getLastName()) $name .= ' ' . $this->getLastName();
            break;

            case self::DISPLAY_LF:
            $name = $this->getLastName();
            if ($this->getFirstName()) $name .= ' ' . $this->getFirstName();
            break;

            case self::DISPLAY_N:
            $name = $this->getNickname();
            break;
        }

        if (!$name) $name = $this->getFullName();

        return $name;
    }

    /**
     * Retrieve author posts
     *
     * @return Collection
     */
    public function getPostCollection()
    {
        if ($this->postCollection === null) {
            $collection = $this->_postCollectionFactory->create();
            $collection->addFieldToFilter('author_id', $this->getId());
            $collection->prepareCollection();
            $collection->addCategoryCollection();
            $collection->addAuthorToCollection();
            $collection->addTotalComments();
            $collection->setOrder('pinned', 'DESC');
            $collection->setOrder('publish_date', 'DESC');
            $this->postCollection = $collection;
        }
        return $this->postCollection;
    }

    /**
     * @return array|mixed|null
     * @throws LocalizedException
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
     * @return string|void
     */
    public function getPath()
    {
        $dataHelper = $this->dataHelper;
        if (!$dataHelper->enableAuthorPage()) return;
        $route = $dataHelper->getRoute();
        $identifier = $route . '/' . $dataHelper->getAuthorRoute() . '/';
        $identifier .= $this->getIdentifier() . $dataHelper->getAuthorUrlSuffix();
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
     * @return string
     */
    public function getImageUrl()
    {
        $image = $this->getData('image');
        if (!$image) $image = 'blog/avatar.png';
        return $this->getMediaUrl() . $image;
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
}