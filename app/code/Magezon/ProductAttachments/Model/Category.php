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
use Magezon\ProductAttachments\Api\Data\CategoryInterface;

class Category extends AbstractModel implements CategoryInterface
{
    /**
     * Category statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLE = 0;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Category::class);
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get category id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * Get category status
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set category name
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::CATEGORY_ID, $id);
    }

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set category status
     *
     * @param bool $status
     * @return $this
     */
    public function setIsActive($status)
    {
        return $this->setData(self::IS_ACTIVE, $status);
    }
}
