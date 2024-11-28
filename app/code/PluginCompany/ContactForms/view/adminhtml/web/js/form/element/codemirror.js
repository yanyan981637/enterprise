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
    'PluginCompany_ContactForms/lib/CodeMirror/lib/codemirror',
    'PluginCompany_ContactForms/lib/CodeMirror/mode/javascript/javascript',
    'PluginCompany_ContactForms/lib/CodeMirror/mode/css/css',
    'css!PluginCompany_ContactForms/lib/CodeMirror/lib/codemirror.css'
],function(
    Abstract,
    $,
    CodeMirror
) {
    return Abstract.extend({
        cEditor: 'ello',
        defaults: {
            language: 'css',
        },

        initialize: function () {
            this._super();
            return this;
        },

        onElementRender: function(em)
        {
            var pScope = this;
            CodeMirror.fromTextArea(em, {
                lineNumbers: true,
                mode: pScope.language
            })
            .on('change',function(a){
                pScope.value(a.getValue())
            })
            ;
            this.updateCodeMirrorElements();
        },

        updateCodeMirrorElements: function()
        {
            setTimeout(function(){
                $('.CodeMirror').each(function(i, el){
                    el.CodeMirror.refresh();
                });
            },100)
        },

        initObservable: function () {
            return this._super();
        }
    });
});
