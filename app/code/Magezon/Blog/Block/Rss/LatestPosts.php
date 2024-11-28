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

namespace Magezon\Blog\Block\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\PostManager;

class LatestPosts extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var PostManager
     */
    protected $postManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param PostManager $postManager
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        PostManager $postManager,
        Data $dataHelper,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->urlBuilder = $urlBuilder;
        $this->postManager = $postManager;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_blog_latest_posts_store_' . $this->getStoreId());
        parent::_construct();
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return ($this->dataHelper->isRssAllowed() && $this->dataHelper->getConfig('rss/latest_posts'));
    }

    /**
     * array
     */
    public function getRssData()
    {
        $storeModel = $this->storeManager->getStore($this->getStoreId());
        $link = $this->urlBuilder->getUrl('blog/feed/index', [
            'type' => 'latest_posts',
            'store_id' => $this->getStoreId(),
        ]);
        $title = __('Latest Posts from %1', $storeModel->getFrontendName());
        $lang = $this->_scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $storeModel
        );
        $data = [
            'title' => (string) $title,
            'description' => (string) $title,
            'link' => $this->getRssLink(),
            'charset' => 'UTF-8',
            'language' => $lang,
        ];
        $collection = $this->postManager->getPostCollection($this->getStoreId());
        $collection->setOrder('publish_date', 'DESC');
        foreach ($collection as $post) {
            $data['entries'][] = [
                'title' => $post->getTitle(),
                'link' => $post->getUrl(),
                'image' => $post->getImageUrl(),
                'description' => (string) $post->getPostExcerpt() ?: $post->getTitle(),
                'lastUpdate' => strtotime($post->getPublishDate()),
            ];
        }
        return $data;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = (int) $this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $storeId;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        $data = [];
        if ($this->isAllowed()) {
            $url = $this->getRssLink();
            $data = ['label' => __('Latest Posts'), 'link' => $url];
        }
        return $data;
    }

    /**
     * @return false
     */
    public function isAuthRequired()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getRssLink()
    {
        return $this->dataHelper->getBlogUrl();
    }
}
