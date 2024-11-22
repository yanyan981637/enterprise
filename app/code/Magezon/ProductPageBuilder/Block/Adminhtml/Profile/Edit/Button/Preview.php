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
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Block\Adminhtml\Profile\Edit\Button;

class Preview extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getCurrentProfile()->getId()) {
            $data = [
                'label' => __('Preview'),
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'productpagebuilder_profile_form.productpagebuilder_profile_form.preview_modal',
                                    'actionName' => 'toggleModal'
                                ],
                                [
                                    'targetName' => 'productpagebuilder_profile_form.productpagebuilder_profile_form.preview_modal.productpagebuilder_profile_product_grid',
                                    'actionName' => 'render'
                                ]
                            ]
                        ]
                    ]
                ],
                'on_click'   => '',
                'sort_order' => 20
            ];
        }
        return $data;
    }
}
