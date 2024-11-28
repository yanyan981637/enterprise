<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TagRepositoryInterface
{
    /**
     * Save tag.
     *
     * @param \Magezon\Blog\Api\Data\TagInterface $tag
     * @return \Magezon\Blog\Api\Data\TagInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magezon\Blog\Api\Data\TagInterface $tag);

    /**
     * Retrieve tag.
     *
     * @param int $tagId
     * @return \Magezon\Blog\Api\Data\TagInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($tagId);

    /**
     * Retrieve tags matching the specified searchCriteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magezon\Blog\Api\Data\TagSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete tag.
     *
     * @param \Magezon\Blog\Api\Data\TagInterface $tag
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magezon\Blog\Api\Data\TagInterface $tag);

    /**
     * Delete tag by ID.
     *
     * @param int $tagId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($tagId);
}
