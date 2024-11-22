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

interface CategoryInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CATEGORY_ID = 'category_id';
    const NAME = 'name';
    const IS_ACTIVE = 'is_active';

    /**
     * Get category id
     *
     * @return int
     */
    public function getId();

    /**
     * Get category name
     *
     * @return string
     */
    public function getName();

    /**
     * Get category status
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Set category id
     *
     * @param int $id
     * @return Magezon\ProductAttachments\Api\Data\CategoryInterface
     */
    public function setId($id);

    /**
     * Set category nameategory
     *
     * @param string $name
     * @return Magezon\ProductAttachments\Api\Data\CategoryInterface
     */
    public function setName($name);

    /**
     * Set category status
     *
     * @param bool $status
     * @return Magezon\ProductAttachments\Api\Data\CategoryInterface
     */
    public function setIsActive($status);
}
