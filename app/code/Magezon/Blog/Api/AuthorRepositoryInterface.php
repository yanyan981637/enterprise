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

interface AuthorRepositoryInterface
{
    /**
     * Save author.
     *
     * @param \Magezon\Blog\Api\Data\AuthorInterface $author
     * @return \Magezon\Blog\Api\Data\AuthorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magezon\Blog\Api\Data\AuthorInterface $author);

    /**
     * Retrieve author.
     *
     * @param int $authorId
     * @return \Magezon\Blog\Api\Data\AuthorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($authorId);

    /**
     * Retrieve authors matching the specified searchCriteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magezon\Blog\Api\Data\AuthorSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete author.
     *
     * @param \Magezon\Blog\Api\Data\AuthorInterface $author
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magezon\Blog\Api\Data\AuthorInterface $author);

    /**
     * Delete author by ID.
     *
     * @param int $authorId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($authorId);
}
