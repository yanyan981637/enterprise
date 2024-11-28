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

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Api\Data\CommentInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Asset\Repository;

class Comment extends AbstractModel implements CommentInterface
{
    /**
     * Approved comment status code
     */
    const STATUS_APPROVED = 1;

    /**
     * Pending comment status code
     */
    const STATUS_PENDING = 2;

    /**
     * Not Approved comment status code
     */
    const STATUS_NOT_APPROVED = 3;

    /**
     * Blog comment cache tag
     */
    const CACHE_TAG = 'blog_c';

    /**
     * Type native comment
     */
    const TYPE_NATIVE   = 'native';

    /**
     * Type Facebook comment
     */
    const TYPE_FACEBOOK = 'facebook';

    /**
     * Type disqus comment
     */
    const TYPE_DISQUS   = 'disqus';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_comment';

    /**
     * @var Post
     */
    protected $_post;

    /**
     * @var Comment
     */
    protected $_parent;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var PostFactory
     */
    protected $_postFactory;

    /**
     * @var CommentFactory
     */
    protected $commentFactory;

    /**
     * @var Repository
     */
    protected $assetRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param PostFactory $postFactory
     * @param CommentFactory $commentFactory
     * @param Repository $assetRepository,
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        PostFactory $postFactory,
        CommentFactory $commentFactory,
        Repository $assetRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->storeManager    = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->_postFactory    = $postFactory;
        $this->commentFactory  = $commentFactory;
        $this->assetRepository = $assetRepository;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Comment::class);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return parent::getData(self::COMMENT_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return CommentInterface
     */
    public function setId($id)
    {
        return $this->setData(self::COMMENT_ID, $id);
    }

    /**
     * Get post id
     *
     * @return int|null
     */
    public function getPostId()
    {
        return parent::getData(self::POST_ID);
    }

    /**
     * Set post id
     *
     * @param int $postId
     * @return CommentInterface
     */
    public function setPostId($postId)
    {
        return $this->setData(self::POST_ID, $postId);
    }

    /**
     * Get parent id
     *
     * @return int|null
     */
    public function getParentId()
    {
        return parent::getData(self::PARENT_ID);
    }

    /**
     * Set parent id
     *
     * @param int $parentId
     * @return CommentInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return parent::getData(self::AUTHOR);
    }

    /**
     * Set author
     *
     * @param string $author
     * @return CommentInterface
     */
    public function setAuthor($author)
    {
        return $this->setData(self::AUTHOR, $author);
    }

    /**
     * Get author email
     *
     * @return string
     */
    public function getAuthorEmail()
    {
        return parent::getData(self::AUTHOR_EMAIL);
    }

    /**
     * Set author email
     *
     * @param string $authorEmail
     * @return CommentInterface
     */
    public function setAuthorEmail($authorEmail)
    {
        return $this->setData(self::AUTHOR_EMAIL, $authorEmail);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return CommentInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return CommentInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
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
     * @return CommentInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return CommentInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get remote ip
     *
     * @return string|null
     */
    public function getRemoteIp()
    {
        return parent::getData(self::REMOTE_IP);
    }

    /**
     * Set remote ip
     *
     * @param string $remoteIp
     * @return CommentInterface
     */
    public function setRemoteIp($remoteIp)
    {
        return $this->setData(self::REMOTE_IP, $remoteIp);
    }

    /**
     * Get brower
     *
     * @return string|null
     */
    public function getBrower()
    {
        return parent::getData(self::BROWER);
    }

    /**
     * Set brower
     *
     * @param string $brower
     * @return CommentInterface
     */
    public function setBrower($brower)
    {
        return $this->setData(self::BROWER, $brower);
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
     * @return CommentInterface
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
     * @return CommentInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Check if current comment approved or not
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatus() == self::STATUS_APPROVED;
    }

    /**
     * Get statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        return [
            self::STATUS_APPROVED     => __('Approved'),
            self::STATUS_PENDING      => __('Pending'),
            self::STATUS_NOT_APPROVED => __('Not Approved')
        ];
    }

    /**
     * Get statuses
     *
     * @return array
     */
    public function getCommentTypes()
    {
        return [
            self::TYPE_NATIVE   => __('Native Comment'),
            self::TYPE_FACEBOOK => __('Facebook'),
            self::TYPE_DISQUS   => __('Disqus')
        ];
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        $image = $this->getData('image');
        if (!$image) {
            $image = $this->assetRepository->getUrl('Magezon_Blog::images/avatar.png');
            return $image;
        }
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

    /**
     * @return string
     */
    public function getCreatedAtFormatted()
    {
        $dateFormat = 'F j, Y \\a\\t H:i';
        $date = $this->getCreationTime();
        if ($this->getPublishDate()) $date = $this->getPublishDate();
        return date($dateFormat, strtotime("now"));
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        if ($this->_post === NULL) {
            $post = $this->_postFactory->create();
            $post->load($this->getPostId());
            $this->_post = $post;
        }
        return $this->_post;
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post)
    {
        $this->_post = $post;
        return $this;
    }

    /**
     * @return Comment
     */
    public function getParent()
    {
        if ($this->_parent === NULL) {
            $this->_parent = false;
            if ($parentId = $this->getParentId()) {
                $parent = $this->commentFactory->create();
                $parent->load($parentId);
                if ($parent->getId()) $this->_parent = $parent;
            }
        }
        return $this->_parent;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        if ($this->_customer === NULL) {
            $this->_customer = false;
            if ($customerId = $this->getCustomerId()) {
                $customer = $this->customerFactory->create();
                $customer->load($customerId);
                if ($customer->getId()) $this->_customer = $customer;
            }
        }
        return $this->_customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getPost()->getUrl() . '#comment-' . $this->getId();
    }

    /**
     * @return string
     */
    public function getContentShow()
    {
        $content = $this->getContent();
        $contentLimit = substr($content, 0, 1000);
        $contentLimitNoSpaces = str_replace(" ", "", $contentLimit);
        $contentNoSpaces = str_replace(" ", "", $content);
        if(strlen($contentLimitNoSpaces) < strlen($contentNoSpaces)) {
            $contentLimit = substr($contentLimit, 0, strrpos($contentLimit, " "));
            return trim($contentLimit);
        }
        return trim($content);
    }

    /**
     * @return bool
     */
    public function isShowMore() {
        $content = $this->getContent();
        $contentLimit = substr($content, 0, 1000);
        $contentLimitNoSpaces = str_replace(" ", "", $contentLimit);
        $contentNoSpaces = str_replace(" ", "", $content);
        if(strlen($contentLimitNoSpaces) < strlen($contentNoSpaces)) {
            return true;
        }
        return false;
    }
}
