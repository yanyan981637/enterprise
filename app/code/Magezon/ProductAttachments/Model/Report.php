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

namespace Magezon\ProductAttachments\Model;

use Magento\Framework\Model\AbstractModel;
use Magezon\ProductAttachments\Api\Data\ReportInterface;

class Report extends AbstractModel implements ReportInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Report::class);
    }

    /**
     * Get report id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::REPORT_ID);
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getData(self::NAME_FILE);
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Get file id
     *
     * @return int
     */
    public function getFileId()
    {
        return $this->getData(self::FILE_ID);
    }

    /**
     * Get create time report
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Set report id
     *
     * @param $id
     * @return $this
     */
    public function setFileId($id)
    {
        return $this->setData(self::FILE_ID, $id);
    }

    /**
     * Set file name
     *
     * @param $name
     * @return $this
     */
    public function setFileName($name)
    {
        return $this->setData(self::NAME_FILE, $name);
    }

    /**
     * Set store id
     *
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set customer id
     *
     * @param $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set create time report
     *
     * @param $time
     * @return $this
     */
    public function setCreationTime($time)
    {
        return $this->setData(self::CREATION_TIME, $time);
    }
}
