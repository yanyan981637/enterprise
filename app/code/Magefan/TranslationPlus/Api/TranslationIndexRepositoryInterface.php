<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Api;

/**
 * Interface TranslationIndexRepositoryInterface
 */
interface TranslationIndexRepositoryInterface
{

    /**
     * @param Data\TranslationIndexInterface $TranslationIndex
     * @return mixed
     */
    public function save(
        \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface $TranslationIndex
    );

    /**
     * Retrieve TranslationIndex
     *
     * @param  string $TranslationIndexId
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($TranslationIndexId);

    /**
     * Retrieve TranslationIndex matching the specified criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete TranslationIndex
     *
     * @param  \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface $TranslationIndex
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface $TranslationIndex
    );

    /**
     * Delete TranslationIndex by ID
     *
     * @param  string $TranslationIndexId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($TranslationIndexId);
}
