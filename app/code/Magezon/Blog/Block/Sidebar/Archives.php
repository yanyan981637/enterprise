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

namespace Magezon\Blog\Block\Sidebar;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Post;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class Archives extends Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext       = $httpContext;
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags'     => [Post::CACHE_TAG]
        ]);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cache = [
            'MGZ_BLOG_SIDEBAR_ARCHIVES',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
        ];
        return $cache;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === NULL) {
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection();
            $collection->addFieldToFilter('is_active', Post::STATUS_ENABLED);
            $collection->setOrder('creation_time', 'DESC');
            $collection->setOrder('publish_date', 'DESC');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    public function getArchives()
    {
        $collection = $this->getCollection();
        $archives = [];
        foreach ($collection as $post) {
            $date  = strtotime($post->getPublishDate());
            $year  = date('Y', $date);
            $month = date('m', $date);
            if (isset($archives[$year][$month])) {
                $archives[$year][$month]['count']++;
            } else {
                $archives[$year][$month]['count'] = 1;
            }
            $archives[$year][$month]['label'] = __(date('F', $date)).' '.date('Y', $date);
            $archives[$year][$month]['month'] = $month;
            $archives[$year][$month]['year']  = $year;
            $archives[$year][$month]['url']   = $this->dataHelper->getArchiveUrl($month, $year);
        }
        return $archives;
    }
}