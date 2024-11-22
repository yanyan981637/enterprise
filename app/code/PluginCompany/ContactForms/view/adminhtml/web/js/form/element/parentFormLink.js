requirejs.config({
    map: {
        '*': {
            'css': 'PluginCompany_ContactForms/lib/require-css/css'
        }
    }
});
define([
    'PluginCompany_ContactForms/js/form/element/htmlBox',
    'jquery',
    'underscore',
    'mage/url',
    'ko',
    'css!PluginCompany_ContactForms/css/htmlBox'
],function(
    htmlBox,
    $,
    _,
    urlBuilder,
    ko
) {
    return htmlBox.extend({
        defaults: {
            elementTmpl: 'PluginCompany_ContactForms/form/element/parentFormLink'
        },

        initialize: function () {
            this._super();

            var data =this.source.data;
            this.title = ko.observable(data.form_title);
            this.href = ko.observable(data.form_edit_url);

            return this;
        },

        getLinkUrl: function() {
            var id = this.value();
            var path = this.url;
            var idParam = this.url_id;
            return BASE_URL + path + '/' + idParam + '/' + id;
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
