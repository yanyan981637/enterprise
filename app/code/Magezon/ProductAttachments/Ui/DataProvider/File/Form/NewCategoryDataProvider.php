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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Ui\DataProvider\File\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magezon\ProductAttachments\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\UrlInterface;

class NewCategoryDataProvider extends AbstractDataProvider
{
    /**
     * @var \Magezon\ProductAttachments\Model\ResourceModel\Category\Collection
     */
    protected $collection;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getData()
    {
        $this->data = array_replace_recursive(
            $this->data,
            [
                'config' => [
                    'data' => [
                        'return_session_messages_only' => 1
                    ]
                ]
            ]
        );

        return $this->data;
    }
}
