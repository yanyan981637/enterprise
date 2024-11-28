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

namespace Magezon\ProductAttachments\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezon\ProductAttachments\Model\ResourceModel\Category;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'category_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magezon\ProductAttachments\Model\Category::class,
            Category::class
        );
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('main_table.is_active', (int)$isActive ? 1 : 0);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }
}
