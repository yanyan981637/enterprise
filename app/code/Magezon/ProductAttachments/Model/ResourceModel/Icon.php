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


namespace Magezon\ProductAttachments\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magezon\ProductAttachments\Model\IconUploader;

class Icon extends AbstractDb
{
    /**
     * @var IconUploader
     */
    protected $iconUploader;

    /**
     * Icon constructor.
     * @param Context $context
     * @param IconUploader $iconUploader
     */
    public function __construct(
        Context $context,
        IconUploader $iconUploader
    ) {
        $this->iconUploader = $iconUploader;
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgz_product_attachments_icon', 'icon_id');
    }

    /**
     * Remove file data after delete
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(AbstractModel $object)
    {
        $this->iconUploader->deleteImage($object->getFileName());
        return $this;
    }
}
