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
            elementTmpl: 'PluginCompany_ContactForms/form/element/htmlBox'
        },

        initialize: function () {
            this._super();
            return this;
        },

        onElementRender: function(em)
        {
            return this;
        },

        initObservable: function () {
            return this._super();
        }
    });
});
