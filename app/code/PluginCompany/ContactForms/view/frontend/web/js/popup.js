/**
 *
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 *
 */
requirejs.config({
    map: {
        '*': {
            'css': 'PluginCompany_ContactForms/lib/require-css/css'
        }
    }
});
define([
    'underscore',
    'jquery',
    'mageUtils',
    'PluginCompany_ContactForms/js/form',
    'PluginCompany_ContactForms/js/lib/alertify/alertify',
    'css!PluginCompany_ContactForms/js/lib/alertify/css/alertify',
    'css!PluginCompany_ContactForms/js/lib/alertify/css/themes/default',
    'css!PluginCompany_ContactForms/css/shared'
], function (_, $, utils, Form, alertify) {
    'use strict';

    alertify.defaults.maintainFocus = false;
    alertify.defaults.preventBodyShift = false;

    alertify.genericDialog || alertify.dialog('genericDialog',function(options){
        return {
            main:function(content, maxWidth){
                this.setContent(content);
                this.maxWidth = maxWidth;
            },
            setup:function(){
                return {
                    focus:{
                        element:function(){
                            return this.elements.body.querySelector(this.get('selector'));
                        },
                        select:true
                    },
                    options:{
                        basic:true,
                        maximizable:false,
                        moveBounded:true,
                        resizable:false,
                        padding:false,
                        frameless: true,
                        transition: "zoom",
                        autoReset:false
                    },
                };
            },
            settings:{
                selector:undefined
            },
            hooks: {
                // triggered when the dialog is shown, this is seperate from user defined onshow
                onshow: function () {
                    $(this.elements.dialog).css('max-width', this.maxWidth);
                    this.bindEventListeners();
                },
                onclose: function () {
                    this.unbindEventListeners();
                }
            },
            bindEventListeners: function() {
                var that = this;
                $(this.elements.root).find('form').on("pccfBeforeSerialize", function(event,form, formObject){
                    var fWrap = form.closest('.pccf');
                    fWrap
                        .css('max-height', fWrap.outerHeight() + 'px');
                    setTimeout(function() {
                        fWrap.css('max-height', '500px');
                    },1)
                })
                $(this.elements.root).find('form').on("pccfFail", function(event,form, formObject){
                    form.closest('.pccf')
                        .css('max-height', 'none')
                    ;
                })
                $(this.elements.root).find('form').on("pccfSuccess", function(event,form, formObject){
                    if(!formObject.shouldHideAfterSubmit()) return;
                    setTimeout(function(){
                        that.close();
                    },2000)
                })
                return this;
            },
            unbindEventListeners: function() {
                $(this.elements.root).find('form').off("pccfFail");
                $(this.elements.root).find('form').off("pccfBeforeSerialize");
                $(this.elements.root).find('form').off("pccfSuccess");
                return this;
            },
        };
    });

    return Form.extend({
        scrollToTopAfterSubmit: false,
        render: function(){
            window.thisform = this;
            this
                .initPopupEm()
                .initButton()
                .renderButton()
                .attachPopupHandlerToButton()
            ;
            return this._super();
        },
        initPopupEm: function() {
            this.popupEm = this.em.find('.pccf')[0];
            return this;
        },
        initButton: function() {
            this.button = this.getNewButton();
            return this;
        },
        getNewButton: function() {
            var btn = this.getBtnBaseEm();
            btn.find('span').html(this.getButtonText());
            return btn;
        },
        getBtnBaseEm: function() {
            if(this.showLinkAsButton()){
                return $('<button><span /></button>')
                    .attr('type', 'button')
                    .addClass('action primary pccf-popup-btn');
                ;
            }
            else{
                return $('<a><span /></a>')
                    .addClass('pccf_popuplink');
            }
        },
        showLinkAsButton: function() {
            return parseInt(this.config.widget.display_as_button, 10);
        },
        getButtonText: function() {
            return this.config.widget.link_title;
        },
        renderButton: function() {
            this.em.prepend(this.button);
            if(this.shouldWrapInBlock()){
                this.wrapButtonInBlock();
            }
            return this;
        },
        shouldWrapInBlock: function() {
            return parseInt(this.config.widget.wrap_in_block,10);
        },
        wrapButtonInBlock: function() {
            var wrapper =
                $('<div class="block block-wishlist"/>')
                    .append(
                        $('<div />')
                            .addClass('block-title')
                            .html('<strong>' + this.config.widget.widget_title + '</strong>')
                    ).append(
                        $('<div />')
                            .addClass('block-content')
                            .html('<p>' + this.getBlockText() + '</p>')
                );
            wrapper.insertBefore(this.button);
            this.button.appendTo(wrapper.find('.block-content'));
            return this;
        },
        getBlockText: function() {
            var text = this.nl2br(this.config.widget.link_text);
            if(text == "undefined"){
                text = '';
            }
            return text;
        },
        nl2br: function (str, is_xhtml) {
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        },
        attachPopupHandlerToButton: function() {
            var that = this;
            this.button.click(function() {
                that.showPopup();
            })
        },
        showPopup: function() {
            alertify.genericDialog(
                this.popupEm,
                this.getPopupMaxWidth()
            )
            ;
        },
        getPopupMaxWidth: function() {
            var maxWidth = this.config.widget.popup_max_width;
            if(!maxWidth) {
                maxWidth = '500px';
            }
            return maxWidth;
        },
        shouldHideAfterSubmit: function() {
            return parseInt(this.config.widget.auto_hide_popup, 10);
        }


    });
});
