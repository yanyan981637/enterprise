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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Block\Adminhtml\Comment\Edit\Button;

class Save extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->_isAllowedAction('Magezon_Blog::comment_save')) {
            return [];
        }
        return [
            'label' => __('Save'),
            'class' => 'save primary'
        ];
    }
}
