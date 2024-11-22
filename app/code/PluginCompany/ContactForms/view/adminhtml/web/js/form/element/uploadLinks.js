requirejs.config({
    map: {
        '*': {
            'css': 'PluginCompany_ContactForms/lib/require-css/css'
        }
    }
});
define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'underscore',
    'css!PluginCompany_ContactForms/css/htmlBox'
],function(
    Abstract,
    $,
    _
) {
    return Abstract.extend({
        defaults: {
            elementTmpl: 'PluginCompany_ContactForms/form/element/uploadLinks'
        },

        initialize: function () {
            this._super();
            if(!this.value().length) {
                this.value(false);
            };
            return this;
        },

        initObservable: function () {
            return this._super();
        }
    });
});
