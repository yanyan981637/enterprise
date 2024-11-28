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

namespace Magezon\Blog\Block\Adminhtml\Author\Edit\Button;

class Delete extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getCurrentAuthor()->getId() && $this->_isAllowedAction('Magezon_Blog::author_delete')
            && !$this->registry->registry('blog_profile')
        ) {
            $data = [
                'label'    => __('Delete'),
                'class'    => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getUrl('*/*/delete', ['author_id' => $this->getCurrentAuthor()->getId()]) .
                    '\')',
                'sort_order' => 20
            ];
        }
        return $data;
    }
}
