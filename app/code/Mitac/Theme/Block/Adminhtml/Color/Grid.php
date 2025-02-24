<?php
namespace Mitac\Theme\Block\Adminhtml\Color;

use Magento\Backend\Block\Widget\Grid\Container;

class Grid extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_color';
        $this->_blockGroup = 'Mitac_Theme';
        $this->_headerText = __('Manage Colors');
        $this->_addButtonLabel = __('Add New Color');
        parent::_construct();
    }
} 