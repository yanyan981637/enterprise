/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
/* global Base64 */
define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader'
], function ($, FileUploader) {
    'use strict';

    return FileUploader.extend({
        defaults: {
            modules: {
                extension: '${ $.parentName }.container_file.extension'
            }
        },

        addFile: function (file) {
            var extension = file.name.substr(file.name.lastIndexOf('.') + 1);
            this._super();
            this.extension().value(extension);
            return this;
        }
    });
});
