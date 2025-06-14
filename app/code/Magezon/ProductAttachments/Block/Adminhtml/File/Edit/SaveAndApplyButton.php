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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Block\Adminhtml\File\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndApplyButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        return [
            'id_hard' => 'save_apply',
            'label' => __('Save and Apply'),
            'on_click' => '',
            'data_attribute' => $this->getButtonAttribute([true, ['auto_apply' => 1]])
        ];
    }
}
