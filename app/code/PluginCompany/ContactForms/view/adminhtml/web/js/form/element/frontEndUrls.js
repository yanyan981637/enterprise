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
    'ko',
    'css!PluginCompany_ContactForms/css/htmlBox'
],function(
    Abstract,
    $,
    _,
    ko
) {
    return Abstract.extend({
        defaults: {
            elementTmpl: 'PluginCompany_ContactForms/form/element/frontEndUrls',
            imports: {
                urlKey: '${ $.provider }:data.url_key',
                baseUrls: '${ $.provider }:data.form_page_store_base_urls',
                suffix: '${ $.provider }:data.form_page_url_suffix',
                formId: '${ $.provider }:data.entity_id',
                frontendPageEnabledDefault: '${ $.provider }:data.form_page_default_enabled',
                frontendPage: '${ $.provider }:data.frontend_page'
            },
            tracks: {
                frontendPage: true,
                urlKey: true
            }
        },

        initialize: function () {
            this.isUrlKeyUnchanged = ko.observable(1);
            this._super();
            this
                .initFormId()
                .initComputed()
                .updateUrls()
                .initListeners()
            ;
            return this;
        },
        initFormId: function() {
            if(typeof this.formId == 'undefined') {
                this.formId = false;
            }
            return this;
        },
        initComputed: function() {
            this
                .initIsUrlKeyUnchangedComputed()
                .initFormPageEnabledComputed()
            ;
            return this;

        },
        initFormPageEnabledComputed: function() {
            this.formPageEnabled = ko.computed(function() {
                if(this.frontendPage == 1){
                    return true;
                }
                if(this.frontendPage == 2 && this.frontendPageEnabledDefault){
                    return true;
                }
                return false;
            }, this);
            return this;
        },
        initIsUrlKeyUnchangedComputed: function() {
            this.oldUrlKey = this.urlKey;
            this.urlKeyUnchanged = ko.computed(function() {
                return this.oldUrlKey == this.urlKey;
            }, this);
            return this;
        },
        updateUrls: function() {
            this.value(this.getUrls())
            return this;
        },
        getUrls: function() {
            if(!this.urlKey || !this.formId){
                return false;
            }
            var urls = [];
            var that = this;
            $.each(this.baseUrls, function(k, v){
                urls.push(
                    {'url': v + that.getUrlKeyWithSuffix()}
                )
            })
            return urls;
        },
        getUrlKeyWithSuffix: function() {
          return this.urlKey + this.getSuffix();
        },
        getSuffix: function() {
            if(typeof this.suffix == "undefined"){
                return '';
            }
            return this.suffix;
        },
        initListeners: function() {
            var that = this;
            this.on('urlKey', function(urlKey){
                that.updateUrls();
            })
            return this;
        },
        initObservable: function () {
            return this._super();
        }
    });
});
