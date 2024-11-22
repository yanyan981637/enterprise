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
            previewTmpl: 'Magezon_ProductAttachments/ui/form/element/uploader/preview'
        },

        /**
         * Initializes file uploader plugin on provided input element.
         *
         * @param {HTMLInputElement} fileInput
         * @returns {FileUploader} Chainable.
         */
        initUploader: function (fileInput) {
            this.$fileInput = fileInput;

            _.extend(this.uploaderConfig, {
                dropZone:   $(fileInput).closest(this.dropZone),
                change:     this.onFilesChoosed.bind(this),
                drop:       this.onFilesChoosed.bind(this),
                add:        this.onBeforeFileUpload.bind(this),
                done:       this.onFileUploaded.bind(this),
                start:      this.onLoadingStart.bind(this),
                stop:       this.onLoadingStop.bind(this),
                url: 'upload'
            });

            $(fileInput).fileupload(this.uploaderConfig);
            this.setInitialValue();
            return this;
        },

        /**
         * Defines initial value of the instance.
         *
         * @returns {FileUploader} Chainable.
         */
        setInitialValue: function () {
            var value = this.getInitialValue();
            if (typeof value == 'string') {
                value = [{
                    name: value,
                    type: 'image/png',
                    url: window.mgzMediaUrl + "productattachments/files/" + value
                }];
            }
            value = value.map(this.processFile, this);

            this.initialValue = value.slice();

            this.value(value);
            this.on('value', this.onUpdate.bind(this));
            this.isUseDefault(this.disabled());
            return this;
        },
    });
});
