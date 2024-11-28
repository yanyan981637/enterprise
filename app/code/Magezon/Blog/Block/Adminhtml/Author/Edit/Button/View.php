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

class View extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getCurrentAuthor()->getId()) {
            if ($this->registry->registry('blog_profile')) {
                $data = [
                    'label'      => __('View Profile'),
                    'class'      => 'view',
                    'on_click'   => 'window.open(\'' . $this->getViewUrl() . '\', \'_blank\')',
                    'sort_order' => 20
                ];
            } else {
                $data = [
                    'label'      => __('View Author'),
                    'class'      => 'view',
                    'on_click'   => 'window.open(\'' . $this->getViewUrl() . '\', \'_blank\')',
                    'sort_order' => 20
                ];
            }

            
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getViewUrl()
    {
        return $this->getCurrentAuthor()->getUrl();
    }
}