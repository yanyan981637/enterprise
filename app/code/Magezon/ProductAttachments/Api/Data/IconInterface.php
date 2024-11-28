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

interface IconInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ICON_ID = 'icon_id';
    const NAME_TYPE = 'name_type';
    const FILE_NAME = 'file_name';
    const IS_ACTIVE = 'is_active';

    /**
     * Get icon id
     *
     * @return int
     */
    public function getId();

    /**
     * Get file type
     *
     * @return string
     */
    public function getFileType();

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName();

    /**
     * Get icon status
     *
     * @return string
     */
    public function getIsActive();

    /**
     * Set icon id
     *
     * @param $id
     * @return int
     */
    public function setId($id);

    /**
     * Set file type
     *
     * @param $type
     * @return string
     */
    public function setFileType($type);

    /**
     * Set file name
     *
     * @param $name
     * @return string
     */
    public function setFileName($name);

    /**
     * Set icon status
     *
     * @param $status
     * @return bool
     */
    public function setIsActive($status);
}
