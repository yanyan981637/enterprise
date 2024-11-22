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

namespace Magezon\ProductAttachments\Block\Adminhtml;

class TopMenu extends \Magezon\Core\Block\Adminhtml\TopMenu
{
    /**
     * Init menu items
     *
     * @return array
     */
    public function intLinks()
    {
        $links = [
            [
                [
                    'title' => __('Add New Files'),
                    'link' => $this->getUrl('productattachments/file/new'),
                    'resource' => 'Magezon_ProductAttachments::file'
                ],
                [
                    'title' => __('Manage Files'),
                    'link' => $this->getUrl('productattachments/file'),
                    'resource' => 'Magezon_ProductAttachments::file'
                ]
            ],
            [
                [
                    'title' => __('Add New Icon'),
                    'link' => $this->getUrl('productattachments/icon/new'),
                    'resource' => 'Magezon_ProductAttachments::icon'
                ],
                [
                    'title' => __('Manage Icons'),
                    'link' => $this->getUrl('productattachments/icon'),
                    'resource' => 'Magezon_ProductAttachments::icon'
                ]
            ],
            [
                [
                    'title' => __('Add New Category'),
                    'link' => $this->getUrl('productattachments/category/new'),
                    'resource' => 'Magezon_ProductAttachments::category'
                ],
                [
                    'title' => __('Manage Categories'),
                    'link' => $this->getUrl('productattachments/category'),
                    'resource' => 'Magezon_ProductAttachments::category'
                ]
            ],
            [
                [
                    'title' => __('Reports'),
                    'link' => $this->getUrl('productattachments/report'),
                    'resource' => 'Magezon_ProductAttachments::report'
                ]
            ],
            [
                [
                    'title' => __('Import'),
                    'link' => $this->getUrl('adminhtml/import/index/mgz/true'),
                    'resource' => 'Magezon_ProductAttachments::import'
                ]
            ],
            [
                [
                    'title' => __('Settings'),
                    'link' => $this->getUrl('adminhtml/system_config/edit/section/mgzattach'),
                    'resource' => 'Magezon_ProductAttachments::settings'
                ]
            ],
            [
                'class' => 'separator'
            ],
            [
                'title' => __('User Guide'),
                'link' => 'https://magezon.com/pub/media/productfile/productattachments-user_guides.pdf',
                'target' => '_blank'
            ],
            [
                'title' => __('Change Log'),
                'link' => 'https://www.magezon.com/magento-2-product-attachments.html#release_notes',
                'target' => '_blank'
            ],
            [
                'title' => __('Get Support'),
                'link' => $this->getSupportLink(),
                'target' => '_blank'
            ]
        ];
        return $links;
    }
}
