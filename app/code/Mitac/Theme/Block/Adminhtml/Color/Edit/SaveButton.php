<?php

namespace Mitac\Theme\Block\Adminhtml\Color\Edit;

class SaveButton extends Generic
{
    public function getButtonData():array
    {
        return [
            'label' => __('Save'),
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'class' => 'save primary',
            'name' => 'save',
        ];
    }

}
