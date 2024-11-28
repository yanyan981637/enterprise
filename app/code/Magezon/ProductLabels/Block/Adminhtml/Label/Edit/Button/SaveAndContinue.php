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
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductLabels\Block\Adminhtml\Label\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magezon\ProductLabels\Block\Adminhtml\Label\Edit\Button\Generic;

class SaveAndContinue extends Generic implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->_isAllowedAction('Magezon_ProductLabels::label_save')) {
            $data = [
                'id_hard'        => 'save_and_continue',
                'label'          => __('Save and Continue'),
                'on_click'       => '',
                'data_attribute' => $this->getButtonAttribute([ true, ['save_and_continue' => 1]])
            ];
        }
        return $data;
    }
}
