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
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Block\Adminhtml\Profile\Edit\Button;

class Delete extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getCurrentProfile()->getId() &&
            $this->_isAllowedAction('Magezon_ProductPageBuilder::profile_delete')) {
            $data = [
                'label'    => __('Delete'),
                'class'    => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getUrl('*/*/delete', ['profile_id' => $this->getCurrentProfile()->getId()]) . '\')',
                'sort_order' => 20
            ];
        }
        return $data;
    }
}
