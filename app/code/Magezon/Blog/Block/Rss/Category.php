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
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\CategoryFactory;

class Category extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magezon\Blog\Model\Category
     */
    protected $_category;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_blog_category_' . $this->getRequest()->getParam('id'));
        parent::_construct();
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return ($this->dataHelper->isRssAllowed() && $this->dataHelper->getConfig('rss/category'));
    }

    /**
     * @return \Magezon\Blog\Model\Category
     */
    public function getCategory()
    {
        if ($this->_category === null) {
            $id = (int) $this->getRequest()->getParam('id');
            $category = $this->categoryFactory->create();
            $category->load($id);
            $this->_category = $category;
        }
        return $this->_category;
    }

    /**
     * array
     */
    public function getRssData()
    {
        $category = $this->getCategory();
        if (!$category->getId() || !$category->isActive()) {
            return [
                'title' => 'Category Not Found',
                'description' => 'Category Not Found',
                'link' => $this->dataHelper->getBlogUrl(),
                'charset' => 'UTF-8',
            ];
        }
        $data = [
            'title' => $category->getTitle(),
            'description' => $category->getTitle(),
            'link' => $category->getUrl(),
            'charset' => 'UTF-8',
        ];
        $collection = $category->getPostCollection();
        foreach ($collection as $post) {
            $data['entries'][] = [
                'title' => $post->getTitle(),
                'link' => $post->getUrl(),
                'image' => $post->getImageUrl(),
                'description' => $post->getPostExcerpt() ?: $post->getTitle(),
                'lastUpdate' => strtotime($post->getPublishDate()),
            ];
        }
        return $data;
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
        if ($this->isAllowed() && ($category = $this->getCategory()) && $category->getId()) {
            $data = ['label' => $category->getTitle(), 'link' => $category->getUrl()];
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
}
