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

namespace Magezon\ProductAttachments\Model\ResourceModel\Report;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezon\ProductAttachments\Model\ResourceModel\Report;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'report_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magezon\ProductAttachments\Model\Report::class,
            Report::class
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->joinLeft(
            ['cgf' => $this->getTable('customer_grid_flat')],
            'main_table.customer_id = cgf.entity_id',
            [
                'name' => 'name',
                'email' => 'email',
            ]
        )->joinLeft(
            ['mpaf' => $this->getTable('mgz_product_attachments_file')],
            'main_table.file_id = mpaf.file_id',
            [
                'file_label' => 'mpaf.file_label'
            ]
        );

        return parent::_initSelect();
    }
}
