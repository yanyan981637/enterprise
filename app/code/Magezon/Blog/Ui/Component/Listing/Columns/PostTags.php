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

namespace Magezon\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory;

class PostTags extends Column
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ResourceConnection $resource
     * @param CollectionFactory $collectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resource,
        CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $connection = $this->resource->getConnection();
            $select = $connection->select()->from($this->resource->getTableName('mgz_blog_tag_post'));
            $list = $connection->fetchAll($select);
            $catIds = [];
            foreach ($list as $_row) {
                $catIds[] = $_row['tag_id'];
            }
            $tagCollection = $this->collectionFactory->create();
            $tagCollection->addFieldToFilter('tag_id', ['in' => $catIds]);
            foreach ($dataSource['data']['items'] as & $item) {
                $html = '';
                foreach ($list as $_row) {
                    if ($item['post_id'] == $_row['post_id']) {
                        $tag = $tagCollection->getItemById($_row['tag_id']);
                        if ($tag) {
                            if ($html) $html .= ', ';
                            $html .= $tag->getTitle();
                        }
                    }
                }
                $item[$this->getData('name')] = $html;
            }
        }
        return $dataSource;
    }
}
