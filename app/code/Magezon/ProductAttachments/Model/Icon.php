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
 *
 */

namespace Magezon\ProductAttachments\Model;

use Magento\Framework\Model\AbstractModel;
use Magezon\ProductAttachments\Api\Data\IconInterface;

class Icon extends AbstractModel implements IconInterface
{
    /**
     * Icon statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLE = 0;

    /**
     * @var \Magezon\ProductAttachments\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * Icon constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magezon\Core\Helper\Data $coreHelper,
        \Magezon\ProductAttachments\Helper\Data $dataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->coreHelper = $coreHelper;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magezon\ProductAttachments\Model\ResourceModel\Icon::class);
    }

    /**
     * Get file type
     *
     * @return string
     */
    public function getFileType()
    {
        return $this->getData(self::NAME_TYPE);
    }

    /**
     * Get icon id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ICON_ID);
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getData(self::FILE_NAME);
    }

    /**
     * Get icon status
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set icon id
     *
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ICON_ID, $id);
    }

    /**
     * Set file type
     *
     * @param $name
     * @return $this
     */
    public function setFileType($name)
    {
        return $this->setData(self::NAME_TYPE, $name);
    }

    /**
     * Set icon status
     *
     * @param $status
     * @return $this
     */
    public function setIsActive($status)
    {
        return $this->setData(self::IS_ACTIVE, $status);
    }

    /**
     * Set file name
     *
     * @param $name
     * @return $this
     */
    public function setFileName($name)
    {
        return $this->setData(self::FILE_NAME, $name);
    }

    /**
     * Get file icon url
     *
     * @return string
     */
    public function getUrlIcon()
    {
        if ($this->getFileName()) {
            return $this->coreHelper->getMediaUrl() . 'productattachments/icons/' . $this->getFileName();
        }
        return $this->dataHelper->getViewFileUrl('Magezon_ProductAttachments/images/icon.svg');
    }
}
