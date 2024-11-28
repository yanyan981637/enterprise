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
    'css!PluginCompany_ContactForms/css/shared',
    'css!PluginCompany_ContactForms/css/iconfont/css/pccontact',
], function (_, $, utils, Form, alertify) {
    'use strict';

    alertify.defaults.maintainFocus = false;
    alertify.defaults.preventBodyShift = false;

    alertify.slideOut || alertify.dialog('slideOut',function(options){
        return {
            lastModalScrollPosition: 0,
            main:function(content, button, xyPos, animation, autoHide, shouldFixBottom, slideOutType){
                this.setContent(content);
                this.button = button;
                this.xyPos = xyPos;
                this.animation = animation;
                this.autoHide = autoHide;
                this.shouldFixBottom = shouldFixBottom;
                this.slideOutType = slideOutType;
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
                        resizable:false,
                        padding:false,
                        frameless: true,
                        transition: "",
                        autoReset:false,
                        modal: true,
                        moveBounded:true,
                        movable:false,
                        pinnable:false,
                        pinned:false,
                        preventBodyShift: true
                    },
                };
            },
            settings:{
                selector:undefined
            },
            hooks: {
                onshow: function () {
                    this
                        .addBottomSlideOutClasses()
                        .runShowAnimation()
                        .bindEventListeners()
                    ;
                },
                onclose: function () {
                    this
                        .unbindEventListeners()
                        .runHideAnimation()
                    ;
                }
            },
            runShowAnimation: function() {
                $('.ajs-modal').css('overflow', 'hidden');
                setTimeout(function() {
                    $('.ajs-modal').css('overflow', 'auto');
                },500)
                var em = $(this.elements.dialog);
                em.removeClass('hidden');
                if($(window).width() < 570){
                    this.xyPos['x'] = 0;
                }
                if(!this.shouldFixBottom){
                    this.moveTo(this.xyPos['x'],this.xyPos['y']);
                }
                em
                    .addClass('animated')
                    .addClass(this.animation);
                ;
                this.button.fadeOut(0);
                return this;
            },
            addBottomSlideOutClasses: function() {
                $(this.elements.dialog)
                    .removeClass('slideOutBottom')
                    .removeClass('slideOutBottomRight')
                    .removeClass('slideOutBottomLeft')
                ;
                if(!this.shouldFixBottom) return this;

                $(this.elements.dialog).addClass('slideOutBottom');

                if(this.slideOutType == 'bottom_right'){
                    $(this.elements.dialog).addClass('slideOutBottomRight');
                }else{
                    $(this.elements.dialog).addClass('slideOutBottomLeft');
                }
                return this;
            },
            bindEventListeners: function() {
                var that = this;
                $(this.elements.root).find('form').on("pccfSuccess", function(event,form){
                    if(!that.autoHide) return;
                    setTimeout(function(){
                        that.close();
                    },2000)
                })
                return this;
            },
            unbindEventListeners: function() {
                $(this.elements.root).find('form').off("contentUpdate");
                $(this.elements.root).find('form').off("pccfSuccess");
                $(this.elements.root).find('form').off("pccfFail");
                $(this.elements.root).find('form').off("pccfBeforeSerialize");
                return this;
            },
            runHideAnimation: function() {
                var em = $(this.elements.dialog);
                em.addClass('hidden');
                em.removeClass('animated');
                em.removeClass(this.animation);
                this.button.fadeIn();
                return this;
            }
        };
    });

    return Form.extend({
        scrollToTopAfterSubmit: false,
        render: function(){
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
            var btn = $('<a />')
                    .addClass('pccf_slideoutlink')
                    .addClass(this.getButtonCssClass())
                    .css('color', this.getButtonTextColor())
                    .css('background-color', this.getButtonBackgroundColor())
                ;
            if(!this.getButtonTitle())
            {
                btn.html('<i class="demo-icon pc-contact-icon-mail"></i>');
                btn
                    .addClass('iconmail')
                    .removeClass('rotate90')
                    .removeClass('rotate_min90')
            }
            btn.text(this.getButtonTitle());
            return btn;
        },
        getButtonCssClass: function() {
            return this.getCssClassConfig()[this.getSlideOutType()];
        },
        getCssClassConfig: function() {
            return {
                'left': 'slideout_left rotate90',
                'right': 'slideout_right rotate_min90',
                'bottom_left': 'slideout_bottom_left',
                'bottom_right': 'slideout_bottom_right',
            }
        },
        getButtonTextColor: function() {
            return this.config.widget.title_color;
        },
        getButtonBackgroundColor: function() {
            return this.config.widget.button_color;
        },
        getSlideOutType: function() {
            return this.config.widget.slideout_position;
        },
        getButtonTitle: function() {
            return this.config.widget.title;
        },
        renderButton: function() {
            this.em.prepend(this.button);
            this.fixRotatedButtonPosition();
            return this;
        },
        fixRotatedButtonPosition: function() {
            if(this.getSlideOutType() == 'left' && this.getButtonTitle()) {
                this.button.css('left', '-' + (this.button.outerHeight() + 1) + 'px');
            }
            if(this.getSlideOutType() == 'right' && this.getButtonTitle()) {
                this.button.css('right', '-' + (this.button.outerHeight() + 1) + 'px');
            }
            return this;
        },
        attachPopupHandlerToButton: function() {
            var that = this;
            this.button.click(function() {
                that.showPopup();
            })
        },
        showPopup: function() {
            alertify.slideOut(
                this.popupEm,
                this.button,
                this.getSlideOutXYPos(),
                this.getSlideAnimation(),
                this.shouldHideAfterSubmit(),
                this.shouldFixBottom(),
                this.getSlideOutType()
            );
            return this;
        },
        getSlideOutXYPos: function() {
            return this.getSlideOutXYPosConfig()[this.getSlideOutType()];
        },
        getSlideOutXYPosConfig: function() {
            return {
                'left': {
                    x: 0,
                    y: 50
                },
                'right': {
                    x: document.body.clientWidth - 535,
                    y: 50
                },
                'bottom_left': {
                    x: 50,
                    y: 'bottomScreen'
                },
                'bottom_right': {
                    x: $("body").prop("scrollWidth") - 548 - 50,
                    y: 'bottomScreen'
                }
            }
        },
        getSlideAnimation: function() {
            return this.getSlideAnimationConfig()[this.getSlideOutType()];
        },
        getSlideAnimationConfig: function() {
            return {
                'left': 'bounceInLeft',
                'right': 'bounceInRight',
                'bottom_left': 'fadeInUp',
                'bottom_right': 'slideInUp'
            }
        },
        shouldHideAfterSubmit: function() {
            return parseInt(this.config.widget.auto_hide, 10);
        },
        shouldFixBottom: function() {
            return this.getShouldFixBottomConfig()[this.getSlideOutType()];
        },
        getShouldFixBottomConfig: function() {
            return {
                'left': false,
                'right': false,
                'bottom_left': true,
                'bottom_right': true
            }
        },

    });
});
