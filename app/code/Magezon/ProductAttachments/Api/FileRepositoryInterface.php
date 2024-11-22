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

namespace Magezon\ProductAttachments\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magezon\ProductAttachments\Api\Data\FileInterface;

interface FileRepositoryInterface
{
    /**
     * @param \Magezon\ProductAttachments\Api\Data\FileInterface $file
     * @return \Magezon\ProductAttachments\Api\Data\FileSearchResultsInterface
     */
    public function save(FileInterface $file);

    /**
     *  Retrieve file.
     *
     * @param int $fileId
     * @return \Magezon\ProductAttachments\Api\Data\FileInterface
     * @throws LocalizedException
     */
    public function getById($fileId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magezon\ProductAttachments\Api\Data\FileSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param \Magezon\ProductAttachments\Api\Data\FileInterface $file
     * @return bool true on success
     * @throws LocalizedException
     */

    public function delete(FileInterface $file);

    /**
     *  Retrieve file.
     *
     * @param int $fileId
     * @return bool true on success
     * @throws LocalizedException
     */
    public function deleteById($fileId);
}
