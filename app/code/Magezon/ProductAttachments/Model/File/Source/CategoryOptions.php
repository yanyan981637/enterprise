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

namespace Magezon\ProductAttachments\Model\File\Source;

use Magento\Framework\Option\ArrayInterface;
use Magezon\ProductAttachments\Model\ResourceModel\Category\CollectionFactory;

class CategoryOptions implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * CategoryOptions constructor.
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->categoryCollectionFactory->create();
        foreach ($collection as $id => $category) {
            $options[] = [
                'label' => $category->getName(),
                'value' => $id
            ];
        }
        return $options;
    }
}
