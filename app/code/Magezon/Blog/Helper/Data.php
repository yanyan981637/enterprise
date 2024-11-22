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

namespace Magezon\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Model\Comment;
use Magento\Framework\View\Asset\Repository;

class Data extends AbstractHelper
{
    const ROUTER = 'blog';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @var Repository
     */
    protected $assetRepository;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param Registry $registry
     * @param \Magezon\Core\Helper\Data $coreHelper
     * @param Repository $assetRepository
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        Registry $registry,
        \Magezon\Core\Helper\Data $coreHelper,
        Repository $assetRepository

    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->registry = $registry;
        $this->coreHelper = $coreHelper;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @param  string $key
     * @param  null|int $store
     * @return null|string
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();
        $result = $this->scopeConfig->getValue(
            'mgzblog/' . $key,
            ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

    /**
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->getConfig('general/enabled');
    }

    /**
     * @return string|null
     */
    public function getRoute()
    {
        return $this->getConfig('permalink/route');
    }

    /**
     * @return string|null
     */
    public function getDateFormat()
    {
        return $this->getConfig('general/date_format');
    }

    /**
     * @return string|null
     */
    public function getPostUseCategories()
    {
        return $this->getConfig('permalink/post_use_categories');
    }

    /**
     * @return string
     */
    public function getBlogUrl()
    {
        return $this->urlBuilder->getUrl(null, ['_direct' => $this->getRoute()]);
    }

    /**
     * @return string
     */
    public function getBlogTitle()
    {
        return (string) $this->getConfig('latest_page/title');
    }

    /**
     * @return string
     */
    public function getPostUrlSuffix()
    {
        return (string) $this->getConfig('permalink/post_suffix');
    }

    /**
     * @return string
     */
    public function getCategoryRoute()
    {
        return (string) $this->getConfig('permalink/category_route');
    }

    /**
     * @return string
     */
    public function getCategoryUrlSuffix()
    {
        return (string) $this->getConfig('permalink/category_suffix');
    }

    /**
     * @return string
     */
    public function getAuthorRoute()
    {
        return (string) $this->getConfig('permalink/author_route');
    }

    /**
     * @return string
     */
    public function getAuthorUrlSuffix()
    {
        return (string) $this->getConfig('permalink/author_suffix');
    }

    /**
     * @return string
     */
    public function getTagRoute()
    {
        return (string) $this->getConfig('permalink/tag_route');
    }

    /**
     * @return string
     */
    public function getTagUrlSuffix()
    {
        return (string) $this->getConfig('permalink/tag_suffix');
    }

    /**
     * @return string
     */
    public function getSearchRoute()
    {
        return (string) $this->getConfig('permalink/search_route');
    }

    /**
     * @return string|null
     */
    public function getArchiveRoute()
    {
        return $this->getConfig('permalink/archive_route');
    }

    /**
     * @return string
     */
    public function getArchiveUrlSuffix()
    {
        return (string) $this->getConfig('permalink/archive_suffix');
    }

    /**
     * @return int
     */
    public function getDefaultCommentStatus()
    {
        return $this->getConfig('post_page/comments/need_approve') ? Comment::STATUS_PENDING : Comment::STATUS_APPROVED;
    }

    /**
     * @return string|null
     */
    public function isRssAllowed()
    {
        return $this->getConfig('rss/enabled');
    }

    /**
     * @return string|null
     */
    public function enableTagPage()
    {
        return $this->getConfig('tag_page/enabled');
    }

    /**
     * @return string|null
     */
    public function enableAuthorPage()
    {
        return $this->getConfig('author_page/enabled');
    }

    /**
     * @param $month
     * @param $year
     * @return string
     */
    public function getArchiveUrl($month, $year)
    {
        $route = $this->getRoute();
        $identifier = $route . '/' . $this->getArchiveRoute() . '/' . $year . '/' . $month . $this->getArchiveUrlSuffix();
        return $this->urlBuilder->getUrl(null, ['_direct' => $identifier]);
    }

    /**
     * @param $type
     * @param $year
     * @param $month
     * @return Phrase
     */
    public function getArchiveTitle($type, $year, $month = null)
    {
        switch ($type) {
            case 'year':
                $title = __('Yearly Archives: %1', $year);
                break;

            default:
                $startTime = $year . '-' . $month . '-01 00:00:00';
                $_month = __(date('F', strtotime($startTime)));
                $title = __('Monthly Archives: %1', $_month . ' ' . $year);
                break;
        }
        return $title;
    }

    /**
     * @return string|null
     */
    public function getCommentType()
    {
        return $this->getConfig('post_page/comments/type');
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrentLang()
    {
        $store = $this->_storeManager->getStore();
        return $this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return string
     */
    public function getLatestPostsRssLink()
    {
        return $this->urlBuilder->getUrl('blog/feed/index', [
            'type' => 'latest_posts',
            'store_id' => $this->_storeManager->getStore()->getId(),
        ]);
    }

    /**
     * @return bool
     */
    public function isEnabledLatestPostsRss()
    {
        return ($this->getConfig('rss/enabled') && $this->getConfig('rss/latest_posts'));
    }

    /**
     * @return string
     */
    public function getCategoryRssLink()
    {
        return $this->urlBuilder->getUrl('blog/feed/index', [
            'type' => 'blog_category',
            'id' => $this->registry->registry('current_blog_category')->getId(),
        ]);
    }

    /**
     * @return bool
     */
    public function isEnabledCategoryRss()
    {
        return ($this->getConfig('rss/enabled') && $this->getConfig('rss/latest_posts'));
    }

    /**
     * @return string|null
     */
    public function isEnabledSendEmail()
    {
        return $this->getConfig('post_page/comments/send_email_new_comment_enabled');
    }

    /**
     * @param $storeId
     * @return string|null
     */
    public function getEmailTemplateAdmin($storeId = null)
    {
        return $this->getConfig('post_page/comments/admin_email_template', $storeId);
    }

    /**
     * @param $storeId
     * @return string|null
     */
    public function getEmailTemplateCustomer($storeId = null)
    {
        return $this->getConfig('post_page/comments/customer_email_template', $storeId);
    }

    /**
     * @return string|null
     */
    public function isEnabledEmailReplyToAdmin()
    {
        return $this->getConfig('post_page/comments/admin_reply_email_enabled');
    }

    /**
     * @return string|null
     */
    public function getGdprStatus()
    {
        return $this->getConfig('post_page/comments/gdpr');
    }

    /**
     * @return string|null
     */
    public function getGdprPathLink()
    {
        return $this->getConfig('post_page/comments/gdpr_path');
    }

    /**
     * @return string
     */
    public function getLoadingImage()
    {
        return $this->assetRepository->getUrl('Magezon_Blog::images/default/loading.gif');
    }

    /**
     * @return string
     */
    public function getPostListing()
    {
        return $this->getConfig('post_listing/enabled');
    }

    /**
     * @return string|null
     */
    public function getRatingBtnEnable()
    {
        return $this->getConfig('post_page/rating_button/enabled');
    }

    /**
     * @return string|null
     */
    public function getRatingBtnLikeEnable()
    {
        return $this->getConfig('post_page/rating_button/like_button');
    }

    /**
     * @return string|null
     */
    public function getRatingBtnDislikeEnable()
    {
        return $this->getConfig('post_page/rating_button/dislike_button');
    }

    /**
     * @return string|null
     */
    public function getNavLinkEnable()
    {
        return $this->getConfig('post_page/prev_next_post/enabled');
    }

    /**
     * @return string|null
     */
    public function getNavLinkOption()
    {
        return $this->getConfig('post_page/prev_next_post/prev_next_post_option');
    }

    /**
     * @return string|null
     */
    public function getRecaptchaCommentForm()
    {
        return $this->getConfig('post_page/comments/recaptcha/recaptcha_type');
    }

}
