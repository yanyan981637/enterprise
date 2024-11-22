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

interface FileInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const FILE_ID = 'file_id';
    const NAME = 'file_name';
    const LABEL = 'file_label';
    const DESCRIPTION = 'description';
    const CATEGORY_ID = 'category_id';
    const LINK = 'link';
    const IS_ACTIVE = 'is_active';
    const DOWNLOAD_NAME = 'download_name';
    const DOWNLOAD_LIMIT = 'download_limit';
    const ATTACH_TO_EMAIL = 'attach_email';
    const ATTACH_TO_ORDER = 'attach_order';
    const DOWNLOAD_TYPE = 'download_type';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME = 'update_time';
    const FILE_EXTENSION = 'file_extension';
    const FILE_HASH = 'file_hash';
    const FILE_TYPE = 'file_type';
    const IS_BUYER = 'is_buyer';
    const CONTENT = 'content';

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get file name
     *
     * @return string
     */
    public function getName();

    /**
     * Get file label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get file description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get file category id
     *
     * @return string
     */
    public function getCategoryId();

    /**
     * Get file url
     *
     * @return string
     */
    public function getLink();

    /**
     * Get file status
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Get download limit file
     *
     * @return int
     */
    public function getDownloadLimit();

    /**
     * Get status file email
     *
     * @return bool
     */
    public function getAttachToEmail();

    /**
     * Get status file order
     *
     * @return bool
     */
    public function getAttachToOrder();

    /**
     * Get download type
     *
     * @return bool
     */
    public function getDownloadType();

    /**
     * Get download name
     *
     * @return string
     */
    public function getDownloadName();

    /**
     * Get file hash
     *
     * @return string
     */
    public function getFileHash();

    /**
     * Get is buyer
     *
     * @return bool
     */
    public function getIsBuyer();

    /**
     * Get file type
     *
     * @return string
     */
    public function getType();

    /**
     * Set ID
     *
     * @param int $id
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setId($id);

    /**
     * Set file name
     *
     * @param string $name
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setName($name);

    /**
     * Set label file
     *
     * @param string $label
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setLabel($label);

    /**
     * Set file description
     *
     * @param string $description
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setDescription($description);

    /**
     * Set file category id
     *
     * @param int $categoryId
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setCategoryId($categoryId);

    /**
     * Set file url
     *
     * @param string $link
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setLink($link);

    /**
     * Set file status
     *
     * @param bool $isActive
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setIsActive($isActive);

    /**
     * Set download limit/default 0
     *
     * @param int $number
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setDownloadLimit($number);

    /**
     * Set status attach to email
     *
     * @param bool $status
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setAttachToEmail($status);

    /**
     * Set status attach to order
     *
     * @param bool $status
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setAttachToOrder($status);

    /**
     * Set download type
     *
     * @param int $downloadType
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setDownloadType($downloadType);

    /**
     * Set download name
     *
     * @param string $name
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setDownloadName($name);

    /**
     * Set file hash
     *
     * @param string $hash
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setFileHash($hash);

    /**
     * Set pload type
     *
     * @param $type
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setType($type);

    /**
     * Set status buyer
     *
     * @param $isBuyer
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setIsBuyer($isBuyer);

    /**
     * @return string
     */
    public function getContents();

    /**
     * @param string $content
     * @return Magezon\ProductAttachments\Api\Data\FileInterFace
     */
    public function setContents($content);
}
