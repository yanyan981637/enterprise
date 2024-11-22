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
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Model\Config\Source;

class ListProfile implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create();
        $options[] = [
            'value' => '',
            'label' => 'None'
        ];
        foreach ($collection as $profile) {
            $options[] = [
                'value' => $profile->getId(),
                'label' => $profile->getName()
            ];
        }
        return $options;
    }
}
