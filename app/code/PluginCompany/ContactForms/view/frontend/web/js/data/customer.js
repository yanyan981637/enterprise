define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'ko',
    'jquery'
], function (Component, customerData, ko, $) {
    'use strict';


    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            var originalCleanExternalData = ko.utils.domNodeDisposal['cleanExternalData'];
            ko.utils.domNodeDisposal['cleanExternalData'] = function (node) {
                if($(node).closest('.pccf').length) {
                    return;
                }
                originalCleanExternalData();
            };
            this.fields = customerData.get('pc-customer-data');
        },
        getValue: function(key) {
            return key.split(".").reduce(function(o, x) {
                return (typeof o == "undefined" || o === null || typeof o[x] == "undefined" || o[x] == null) ? '' : o[x];
            }, this.fields());
        }
    });
});
