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

namespace Magezon\Blog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface TagSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get tag list.
     *
     * @return \Magezon\Blog\Api\Data\TagInterface[]
     */
    public function getItems();

    /**
     * Set tag list.
     *
     * @param \Magezon\Blog\Api\Data\TagInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
