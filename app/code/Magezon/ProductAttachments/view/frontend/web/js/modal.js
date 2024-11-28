/*
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
 *
 *
 */

define([
        "jquery","jquery/ui", "Magento_Ui/js/modal/modal"
    ], function ($) {
        var Modal = {
            initModal: function (config, element) {
                $target = $(config.target);
                $target.modal({
                    modalClass: 'mpa-popup-wrap'
                });
                $element = $(element);
                $element.click(function () {
                    $target.modal('openModal');
                });
            }
        };

        return {
            'mpa-modal': Modal.initModal
        };
    });