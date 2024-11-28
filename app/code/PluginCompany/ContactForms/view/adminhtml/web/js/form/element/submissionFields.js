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
    'underscore'
],function(
    Abstract,
    $,
    _
) {
    return Abstract.extend({
        defaults: {
            elementTmpl: 'PluginCompany_ContactForms/form/element/submissionFields'
        },

        initialize: function () {
            this._super();
            if(!this.value()) {
                return this;
            }
            var fields = JSON.parse(this.value());
            var fieldArray = _.map(
                _.pairs(fields),
                function(pair) {
                    return {
                        key: pair[0],
                        val: pair[1]
                    };
                }
            );
            this.value(fieldArray);
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
