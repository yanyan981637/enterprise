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

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magezon\Blog\Model\Config\Source\PostsSortBy as PostsSortBySource;

/**
 * Posts Sort By
 */
class PostsSortBy extends Column
{
    /**
     * @var PostsSortBySource
     */
    protected $postsSortBySource;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PostsSortBySource $postsSortBySource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PostsSortBySource $postsSortBySource,
        array $components = [],
        array $data = []
    )
    {
        $this->postsSortBySource = $postsSortBySource;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item) {
                    foreach ($this->postsSortBySource->toOptionArray() as $sortByItem) {
                        if($item['posts_sort_by'] == $sortByItem['value']){
                            $item['posts_sort_by'] = $sortByItem['label'];
                        }
                    }
                }
            }
        }
        return $dataSource;
    }
}