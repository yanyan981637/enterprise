<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Block\Adminhtml\Label\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mgz-labels');
        $this->setDestElementId('edit_form');
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label'   => __('General Information'),
                'title'   => __('General Information'),
                'content' => $this->getLayout()->createBlock('Magezon\ProductLabels\Block\Adminhtml\Label\Edit\Label\Main')->toHtml(),
                'active'  => true,
            ]
        );

        $this->addTab(
            'design',
            [
                'label'   => __('Design'),
                'title'   => __('Design'),
                'content' => $this->getLayout()->createBlock('Magezon\ProductLabels\Block\Adminhtml\Label\Edit\Label\Design')->toHtml()
            ]
        );

        $this->addTab(
            'conditions',
            [
                'label'   => __('Conditions'),
                'title'   => __('Conditions'),
                'content' => $this->getLayout()->createBlock('Magezon\ProductLabels\Block\Adminhtml\Label\Edit\Label\Conditions')->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }
}
