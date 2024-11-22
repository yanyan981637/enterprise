/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define([
    'Magento_Ui/js/form/element/textarea',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'domReady!',
], function (Textarea, $, alert) {
    'use strict';

    return Textarea.extend({

        usePopup: function () {
            if (this.value().length > 25) {
                var mfcomponent = this;
                alert({
                    title: $.mage.__('Translate'),
                    content: '<textarea id="mf_translate" style="width: 100%; height: 450px;">' + this.value() + '</textarea>',
                    actions: {
                        always: function(){
                            var translatedText = document.getElementById("mf_translate").value;
                            mfcomponent.value(translatedText);
                        }
                    }
                });
            }
        },
    });
});
