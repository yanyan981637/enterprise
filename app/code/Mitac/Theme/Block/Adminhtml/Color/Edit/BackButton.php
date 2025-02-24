<?php

namespace Mitac\Theme\Block\Adminhtml\Color\Edit;


class BackButton extends Generic
{
    public function getButtonData(): array
    {
        return [
            'name' => 'back',
            'label' => __('Back'),
            'class' => 'back',
            'onclick' => sprintf("location.href = '%s';", $this->getBackUrl())
        ];
    }

    private function getBackUrl(): string
    {
        return $this->context->getUrl('*/*',['_current' => true]);
    }
}
