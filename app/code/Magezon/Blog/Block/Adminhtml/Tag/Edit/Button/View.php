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

namespace Magezon\Blog\Block\Adminhtml\Tag\Edit\Button;

class View extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getCurrentTag()->getId()) {
            $data = [
                'label'      => __('View Tag'),
                'class'      => 'view',
                'on_click'   => 'window.open(\'' . $this->getViewUrl() . '\', \'_blank\')',
                'sort_order' => 20
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getViewUrl()
    {
        return $this->getCurrentTag()->getUrl();
    }
}