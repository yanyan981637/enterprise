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

namespace Magezon\ProductAttachments\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get category list.
     *
     * @return \Magezon\ProductAttachments\Api\Data\CategoryInterface[]
     */
    public function getItems();

    /**
     * Set category list.
     *
     * @param \Magezon\ProductAttachments\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
