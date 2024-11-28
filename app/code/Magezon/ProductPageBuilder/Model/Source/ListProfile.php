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

namespace Magezon\ProductPageBuilder\Model\Source;

class ListProfile extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @param \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     */
    public function __construct(
        \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
    ) {
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $collection = $this->profileCollectionFactory->create();
        $options = [];

        $options[] = [
            'label' => __('None'),
            'value' => ''
        ];

        foreach ($collection as $profile) {
            $options[] = [
                'label' => $profile->getName(),
                'value' => $profile->getId()
            ];
        }

        return $options;
    }
}
