<?php

namespace Mitac\Theme\Block\Adminhtml\Color\Edit;

class DeleteButton extends Generic
{
    private const RESOURCE = 'Mitac_Theme::color_delete';
    public function getButtonData():array
    {
        if(!$this->_isAllowedAction(self::RESOURCE)){
            return [];
        }

        if(!$this->getColorId()){
            return [];
        }

        return  [
            'label' => __('Delete'),
            'on_click' => sprintf("location.href = '%s';", $this->getDeleteUrl()),
            'class' => 'delete',
            'name' => 'delete',
        ];
    }

    public function getDeleteUrl()
    {
        return $this->context->getUrl('*/*/delete', ['_current' => true, 'color_id' => $this->getColorId()]);
    }
}
