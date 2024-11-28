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

interface ReportInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const REPORT_ID = 'report_id';
    const FILE_ID = 'file_id';
    const NAME_FILE = 'file_name';
    const CUSTOMER_ID = 'customer_id';
    const STORE_ID = 'store_id';
    const CREATION_TIME = 'creation_time';

    /**
     * Get report id
     *
     * @return int
     */
    public function getId();

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName();

    /**
     * Get file id
     *
     * @return int
     */
    public function getFileId();

    /**
     * Get create time report
     *
     * @return string
     */
    public function getCreationTime();

    /**
     * Get customer id
     * @return int
     */
    public function getCustomerId();

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set customer id
     *
     * @param $customerId
     * @return int
     */
    public function setCustomerId($customerId);

    /**
     * Set store id
     *
     * @param $storeId
     * @return int
     */
    public function setStoreId($storeId);

    /**
     * Set report id
     *
     * @param $id
     * @return int
     */
    public function setId($id);

    /**
     * Set file id
     *
     * @param $fileId
     * @return int
     */
    public function setFileId($fileId);

    /**
     * Set file name
     *
     * @param $name
     * @return string
     */
    public function setFileName($name);

    /**
     * Set create time report
     *
     * @param $creationTime
     * @return string
     */
    public function setCreationTime($creationTime);
}
