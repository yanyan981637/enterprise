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
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;

class FeaturedPosts extends Template
{

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magezon\Blog\Model\ResourceModel\Post\Collection
     */
    public function getCollection()
    {
        $numberOfPosts = (int)$this->dataHelper->getConfig('sidebar/featured_posts/number_of_posts');
        if ($this->collection == null) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToSelect(['title', 'image', 'identifier']);
            $collection->prepareCollection();
            $collection->addFieldToFilter('featured', 1);
            if($numberOfPosts) {
                $collection->setPageSize($numberOfPosts);
            }
            $this->collection = $collection;
        }
        return $this->collection;
    }

    /**
     * @return string|null
     */
    public function getFeaturedPostsTitleConfig()
    {
        return $this->dataHelper->getConfig('sidebar/featured_posts/title');
    }

    /**
     * @return string|null
     */
    public function getFeaturedPostsEnabled()
    {
        return $this->dataHelper->getConfig('sidebar/featured_posts/enabled');
    }

}