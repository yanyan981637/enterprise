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

interface FileSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get file list.
     *
     * @return \Magezon\ProductAttachments\Api\Data\FileInterFace[]
     */
    public function getItems();

    /**
     * Set file list.
     *
     * @param \Magezon\ProductAttachments\Api\Data\FileInterFace[] $items
     * @return $this
     */
    public function setItems(array $items);
}
