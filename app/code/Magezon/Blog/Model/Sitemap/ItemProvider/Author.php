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

namespace Magezon\Blog\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\ResourceModel\Author\CollectionFactory;

class Author implements ItemProviderInterface
{
    /**
     * @var SitemapItemInterfaceFactory
     */
    protected $itemFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     */
    public function __construct(
        SitemapItemInterfaceFactory $itemFactory,
        CollectionFactory $collectionFactory,
        Data $dataHelper
    ) {
        $this->itemFactory = $itemFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param $storeId
     * @return array|SitemapItemInterface[]
     */
    public function getItems($storeId)
    {
        $items = [];
        if ($this->dataHelper->getConfig('sitemap/author/enabled')) {
            $collection = $this->collectionFactory->create();
            $items = array_map(function ($item) use ($storeId) {
                return $this->itemFactory->create([
                    'url' => $item->getPath(),
                    'updatedAt' => $item->getUpdateTime(),
                    'images' => $item->getImages(),
                    'priority' => $this->dataHelper->getConfig('sitemap/author/priority'),
                    'changeFrequency' => $this->dataHelper->getConfig('sitemap/author/changefreq'),
                ]);
            }, $collection->getItems());
        }

        return $items;
    }
}
