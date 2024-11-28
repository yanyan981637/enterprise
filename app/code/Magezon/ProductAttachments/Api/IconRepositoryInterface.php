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
use Magezon\ProductAttachments\Api\Data\IconInterface;

interface IconRepositoryInterface
{
    /**
     * @param \Magezon\ProductAttachments\Api\Data\CategoryInterface $icon
     * @return \Magezon\ProductAttachments\Api\Data\IconSearchResultsInterface
     */
    public function save(IconInterface $icon);

    /**
     *  Retrieve icon.
     *
     * @param int $iconId
     * @return \Magezon\ProductAttachments\Api\Data\IconInterface
     * @throws LocalizedException
     */
    public function getById($iconId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magezon\ProductAttachments\Api\Data\IconSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param IconInterface $icon
     * @return bool true on success
     */
    public function delete(IconInterface $icon);

    /**
     *  Retrieve icon.
     *
     * @param int $iconId
     * @return bool true on success
     * @throws LocalizedException
     */
    public function deleteById($iconId);
}
