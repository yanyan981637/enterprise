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

namespace Magezon\ProductAttachments\Ui\Component\Listing\Column\Report;

use Magento\Framework\Option\ArrayInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;

class CustomerName implements ArrayInterface
{
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var Collection
     */
    protected $customerCollection;

    /**
     * Customer constructor.
     *
     * @param CustomerCollectionFactory $customerCollectionFactory
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->customerCollection = $this->customerCollectionFactory->create();

        $options[] = [
            'label' => __('Select Category'),
            'value' => ''
        ];
        foreach ($this->customerCollection as $id => $customer) {
            $options[] = [
                'label' => $customer->getName(),
                'value' => $id
            ];
        }
        return $options;
    }
}
