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
    'uiClass',
    'Magento_Ui/js/lib/spinner',
    'mage/validation',
    'PluginCompany_ContactForms/js/lib/jquery.form',
    'css!PluginCompany_ContactForms/css/shared',
    'css!PluginCompany_ContactForms/css/animate.min',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (_, $, utils, Class, loader) {
    'use strict';

    $.fn.changeElementType = function(newType) {
        this.each(function() {
            var attrs = {};
            $.each(this.attributes, function(idx, attr) {
                attrs[attr.nodeName] = attr.nodeValue;
            });
            $(this).replaceWith(
                $("<" + newType + "/>").attr(attrs)
                    .append($(this).contents())
            );
        });
    };

    return Class.extend({
        em: '',
        form: '',
        config: {},
        moduleName: 'PluginCompany_ContactForms',
        scrollToTopAfterSubmit: true,
        initialize: function(config, node) {
            this.em = $(node);
            this.form = this.em.find('.pccform');
            this.initConfig(config);
            return this.render();
        },
        initObservable: function () {
            return this._super();
        },
        initConfig: function(config) {
            config.dependentFields = JSON.parse(config.dependentFields);
            config.beforeSubmitJs = Function(config.beforeSubmitJs);
            config.submitJs = Function(config.submitJs);
            config.pageloadJs = Function(config.pageloadJs);
            this.config = config;
            this.config.form = this;
            return this;
        },
        render: function() {
            $.when(this.loadTheme())
                 .then(this.renderForm)
            ;
            return this;
        },
        loadTheme: function() {
            var dfd = $.Deferred();

            if(!this.getThemePath()) {
                dfd.resolveWith(this);
            }
            else {
                require(['css!' + this.getThemePath()], _.bind(function(){
                    dfd.resolveWith(this);
                }, this))
            }

            return dfd.promise();
        },
        renderForm: function() {
            this
                .addFormIdInput()
                .applyRtl()
                .initPages()
                .renderSplitColumns()
                .hideLoader()
                .addUniqueIdentifier()
                .changeFormElementType()
                .renderCustomerOrderInvoiceOptions()
                .initValidator()
                .initAjaxForm()
                .initDependentFields()
                .initDateElements()
                .initUploadElements()
                .initCaptcha()
                .runPageloadJs()
            ;
            return this;
        },
        getThemePath: function() {
            if(this.config.theme == 'notheme'){
                return false;
            }
            return this.moduleName + '/css/themes/' + this.config.theme;
        },
        addFormIdInput: function() {
            var inp =
                $('<input />')
                    .attr('type', 'hidden')
                    .attr('name', 'form_id')
                    .val(this.config.formId)
                ;
            this.form.append(inp);
            return this;
        },
        applyRtl: function() {
            if(this.config.rtl){
                //set direction to rtl
                var rtlEm = this.em.find('.rtl');
                rtlEm.css('direction','rtl');
                //modify all elements to rtl
                rtlEm.find('*').each(function(){
                    var el = $(this);
                    var mLeft = el.css('margin-left');
                    var mRight = el.css('margin-right');
                    var pLeft = el.css('padding-left');
                    var pRight = el.css('padding-right');
                    var borderBL = el.css('border-bottom-left-radius');
                    var borderTL = el.css('border-top-left-radius');
                    var borderBR = el.css('border-bottom-right-radius');
                    var borderTR = el.css('border-top-right-radius');
                    if(el.css('float') == 'right'){
                        el[0].style.setProperty('float','left','important');
                    }else if(el.css('float') == 'left'){
                        el[0].style.setProperty('float','right','important');
                    }
                    el[0].style.setProperty('margin-left',mRight,'important');
                    el[0].style.setProperty('margin-right',mLeft,'important');
                    el[0].style.setProperty('padding-left',pRight,'important');
                    el[0].style.setProperty('padding-right',pLeft,'important');
                    el[0].style.setProperty('border-bottom-left-radius',borderBR,'important');
                    el[0].style.setProperty('border-bottom-right-radius',borderBL,'important');
                    el[0].style.setProperty('border-top-left-radius',borderTR,'important');
                    el[0].style.setProperty('border-top-right-radius',borderTL,'important');

                    if(el.is('label')){
                        el[0].style.setProperty('text-align','left','important');
                    }
                    if(el.is('.radio-inline') || el.is('.radio') || el.is('.checkbox-inline') || el.is('.checkbox') || el.is('button')){
                        el[0].style.setProperty('float','right','important');
                    }
                    if(el.is('.help-block')){
                        el[0].style.setProperty('text-align','right','important');
                    }
                    if(el.attr('class') && el.attr('class').match(/col\-/g)){
                        el[0].style.setProperty('float','right','important');
                    }
                })
            }
            return this;
        },
        initPages: function() {
            if(!this.hasPages()){
                return this;
            }
            this
                .renderMultiPageControls()
                .initMultiPagePreventFormSubmitHook()
                .initMultiPageClickActions()
                .initMultiPageNavResizeListener()
            ;

            return this;
        },
        hasPages: function() {
          return this.em.find('.formpage').length > 0;
        },
        renderMultiPageControls: function() {
            var fieldset = this.em.find('fieldset');
            fieldset.addClass('pagefieldset');
            if(!fieldset.children().eq(0).is('.formpage')){
                fieldset.prepend('<div class="formpage" pagetitle="page 1" nexttext="Next" />');
            }
            fieldset.parent().prepend('<ul class="nav-wizard" />');
            fieldset.children('.formpage').each(function(){
                $(this).removeClass('form-group').html('');
                $(this).append($(this).nextUntil('.formpage'));
                $(this).parents('.pccf').find('.nav-wizard').append('<li><a>' + $(this).attr('pagetitle') + '</a></li>');
                var prevNext = $('<div class="form-group"><div class="col-md-offset-3 col-md-6 navbuttons"></div></div>');
                var navb = prevNext.find('.navbuttons');
                navb.append('<div style="margin-left:10px;float:right;"><button style="float:right;" type="button" class="next btn btn-primary">' + $(this).attr('nexttext') + '</button></div>');
                if($(this).attr('prev') == 1){
                    navb.append('<button style="float:right;" type="button" class="prev btn btn-default">' + $(this).attr('prevtext') + '</button>');
                }
                $(this).append(prevNext);
            })
            fieldset.children('.formpage').last().find('.next').remove();
            fieldset.children('.formpage').first().find('.prev').remove();
            fieldset.parent().find('.nav-wizard li').eq(0).addClass('active');
            return this;
        },
        initMultiPagePreventFormSubmitHook: function() {
            $(document).keydown(function (event) {
                if (event.keyCode == 13 && $(event.target).parents('.formpage').length && !$(event.target).is('textarea')) {
                    event.preventDefault();
                    return false;
                }
            });
            return this;

        },
        initMultiPageClickActions: function() {
            this
                .initNextPageClickAction()
                .initPrevPageClickAction()
            ;
            return this;
        },
        initNextPageClickAction: function() {
            var self = this;
            this.em.find('button.next').click(function(){
                self.form.triggerHandler('contentUpdate', [self.form])
                var element = this;
                var form = $(element).parents('.pccf');
                if(form.find('.animated').length){
                    return;
                }
                if(!form.attr('curpage')){
                    form.attr('curpage', 1);
                }

                var validated = true;
                $(element).parents('.formpage').find('input:visible,select:visible,textarea:visible').each(function () {
                    if(!$(this).valid()){
                        validated = false;
                    }
                });

                if(!validated){
                    return;
                }

                var curPage = parseInt(form.attr('curpage'));
                var curIndex = curPage - 1;

                var curPageEm =  form.find('.formpage').eq(curIndex);
                var nextPageEm =  form.find('.formpage').eq(curPage);

                form.attr('curpage', curPage + 1);

                form.find('.nav-wizard li.active').removeClass('active');
                form.find('.nav-wizard li').eq(curIndex + 1).addClass('active');

                form.css('overflow','hidden');
                var fieldset = form.find('fieldset');
                fieldset.css('height',fieldset.height()+'px');

                curPageEm.addClass('animated fadeOutLeft anipage');
                nextPageEm.addClass('animated fadeInRight').show();

                nextPageEm.wrap('<fieldset />');
                var newHeight = nextPageEm.parent().outerHeight() + 'px';
                nextPageEm.unwrap();

                fieldset.css('height', newHeight);

                self.form.trigger("gotoNextPage");

                var timeout = 1200;
                setTimeout(function () {
                    curPageEm.removeClass('animated fadeOutLeft anipage');
                    curPageEm.hide();
                    nextPageEm.removeClass('animated fadeInRight');
                    fieldset.css('height','auto');
                    form.css('overflow','');
                }, timeout);
            })
            return this;
        },
        initPrevPageClickAction: function() {
            var self = this;
            this.em.find('button.prev').click(function(){
                self.form.triggerHandler('contentUpdate', [self.form])
                var form = $(this).parents('.pccf');
                if(form.find('.animated').length){
                    return;
                }
                var curPage = parseInt(form.attr('curpage'));
                var curIndex = curPage - 1;

                var curPageEm =  form.find('.formpage').eq(curIndex);
                var prevPageEm =  form.find('.formpage').eq(curIndex - 1);

                form.attr('curpage', curPage - 1);

                form.find('.nav-wizard li.active').removeClass('active');
                form.find('.nav-wizard li').eq(curIndex - 1).addClass('active');

                form.css('overflow','hidden');

                var fieldset = form.find('fieldset');

                curPageEm.addClass('animated fadeOutRight anipage');
                prevPageEm.addClass('animated fadeInLeft').show();

                self.form.trigger("gotoPrevPage");

                var timeout = 1200;
                setTimeout(function () {
                    curPageEm.removeClass('animated fadeOutRight anipage');
                    curPageEm.hide();
                    prevPageEm.removeClass('animated fadeInRight');
                    form.css('overflow','');
                }, timeout);
            });
            return this;
        },
        initMultiPageNavResizeListener: function() {
            $(window).resize(_.bind(function(){
                this.toggleSmallNav();
            }, this));
            this.toggleSmallNav();
            return this;
        },
        toggleSmallNav: function() {
            var useSmallNav = false;
            var wrapper = this.form.closest('.pccf');
            wrapper.removeClass('smallnav');
            wrapper.find('.nav-wizard > li').each(function () {
                if (!$(this).is("li:first-child")) {
                    if ($(this).offset().top > $(this).prev().offset().top) {
                        useSmallNav = true;
                    }
                }
            });
            if(useSmallNav){
                wrapper.addClass('smallnav');
            }else{
                wrapper.removeClass('smallnav');
            }
            return this;
        },
        renderSplitColumns: function() {
            this.em.find('.pcform-section').each(function(){
                $(this)
                    .find('.sectioncontents')
                    .append($(this).nextUntil('.formpage,.pcform-section'))

                if($(this).hasClass('column_1')){
                    if (
                        $(this).prev().hasClass('column_1_wrap')
                        && $(this).prev().children().length < 2
                    ){
                        $(this).appendTo($(this).prev());
                    }else{
                        $(this).wrap('<div class="column_1_wrap" />');
                    }
                }

                //split in two columns if needed
                if($(this).hasClass('columns_2')){
                    var content = $(this).find('.sectioncontents');
                    var columnOneLenght = Math.ceil(content.children('div.row,div.form-group').length/2);

                    if(!columnOneLenght){
                        return;
                    }

                    if(content.find('button[type="submit"]').length){
                        // columnOneLenght--;
                    }
                    if(content.find('.navbuttons').length){
                        columnOneLenght--;
                    }

                    var col1 = $("<div class='col-md-6' />");
                    var col2 = $("<div class='col-md-6' />");
                    col1.prepend(content.children('div.row,div.form-group').slice(0, columnOneLenght));
                    col2.prepend(content.children('div.row,div.form-group'));
                    content.prepend(col1).append(col2);

                    content.find('button[type="submit"]').closest('.form-group').appendTo(col2);
                    content.find('.navbuttons').closest('.form-group').appendTo(col2);
                }
            });
            return this;

        },
        hideLoader: function() {
            this.em.find('.pccf_loader').fadeOut('normal',function() {
                $(this).next().css({ 'visibility':'visible',opacity:0}).animate({opacity: 1.0});
                $(this).remove();
            });
            return this;
        },
        addUniqueIdentifier: function() {
            this
                .initUid()
                .addUidInput()
                .addUidToFormFields()
            ;
            return this;
        },
        initUid: function() {
            this.uniqueIdentifier = this.s4() + this.s4() + this.s4();
            return this;
        },
        s4: function() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        },
        addUidInput: function() {
            var uidInput =
                    $('<input />')
                        .val(this.uniqueIdentifier)
                        .attr('type', 'hidden')
                        .attr('name', 'uid')
                ;
            this.form.append(uidInput)
            return this;
        },
        addUidToFormFields: function() {
            this.em.find('*').each(_.bind(function(v, em){
                this.addUidToElementAttr(em, 'id');
                this.addUidToElementAttr(em, 'for');
            }, this));

            return this;
        },
        addUidToElementAttr: function(element, attr) {
            if($(element).attr(attr)){
                $(element).attr(attr, $(element).attr(attr) + this.uniqueIdentifier);
            }
            return this;
        },
        initValidator: function() {
            this.form.find('.form-group.required-control input[type=checkbox]').addClass('checkbox required');
            this.form.find('.form-group.required-control input[type=radio]').addClass('required');
            this.form.validate({
                errorClass:'mage-error',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    var errorPlacement = element;
                    // logic for date-picker error placement
                    if (element.hasClass('hasDatepicker')) {
                        errorPlacement = element.siblings('img');
                    }
                    // logic for field wrapper
                    var fieldWrapper = element.closest('.addon');
                    if (fieldWrapper.length) {
                        errorPlacement = fieldWrapper.after(error);
                    }
                    //logic for checkboxes/radio
                    if (element.is(':checkbox') || element.is(':radio')) {
                        errorPlacement = element.closest('.form-group').find('div.checkbox, div.radio, .checkbox-inline, .radio-inline').last();
                    }
                    errorPlacement.after(error);
                }
            });
            var self = this;
            this.form.find('input,textarea,select').keyup(function(){
                self.form.triggerHandler('contentUpdate', [self.form])
                if($(this).hasClass('error')){
                    $(this).valid();
                }
            });
            this.form.find('input,textarea').blur(function(){
                $(this).val(
                    $(this).val().trim()
                );
            })
            this.form.find('input,textarea,select').blur(function(){
                self.form.triggerHandler('contentUpdate', [self.form])
                $(this).valid();
            });
            this.form.find('button[type=submit]').click(function(){
                self.form.triggerHandler('contentUpdate', [self.form])
            })
            return this;
        },
        changeFormElementType: function() {
            this.form.changeElementType('form');
            this.form = this.em.find('form');
            return this;
        },
        renderCustomerOrderInvoiceOptions: function() {
            this.form.find('option').each(function() {
                var opt = $(this);
                var text= opt.text();
                if(text.indexOf('||||') == -1) return;
                text = text.split('||||');
                $.each(text, function(k, v) {
                    opt.before('<option>' + v + '</option>');
                });
                opt.remove();
            })
            return this;
        },
        initAjaxForm: function() {
            var config = this.config;
            var form = this.form;
            var self = this;
            form.ajaxForm({
                beforeSubmit: function () {
                    self.fadeOutFormFieldsets();
                    form.trigger( "pccfBeforeSubmit", [form, self]);
                },
                beforeSerialize: function () {
                    if(self.hasInvisibleReCaptcha() && !grecaptcha.getResponse(self.recaptchaWidgetId)) {
                        window.formToSubmitAfterRecaptchaValidation = self;
                        grecaptcha.execute(self.recaptchaWidgetId);
                        return false;
                    }
                    form.trigger( "pccfBeforeSerialize", [form, self]);
                    config.beforeSubmitJs();
                    form.find('.form-group[disabled="disabled"]').remove();
                },
                success: function (responseText) {
                    config.submitJs();
                    setTimeout(function() {
                        self.showResponseMessage(responseText);
                    }, 500)
                    if(!self.isSuccessResponse(responseText)){
                        self.handleSubmitError(responseText);
                    }else{
                        form.trigger( "pccfSuccess", [form, self]);
                    }
                }
            });
            return this;
        },
        submitFormAfterInvisibleReCaptchaValidation: function() {
            $(window.formToSubmitAfterRecaptchaValidation.form).submit();
        },
        showResponseMessage: function(message) {
            if(typeof message == "object" && message != null){
                this.showMessage(message.message, message.type, true);
            }else if(message != null) {
                this.showMessage(message, 'error', true);
            }
            return this;
        },
        showMessage: function(message, type, hideothers){
            var messageContainer = this.getMessageContainer();
            if(hideothers) {
                messageContainer.empty();
            }
            this.insertMessage(message, type);
            if(this.scrollToTopAfterSubmit) {
                this.scrollToMessageContainer();
            }
            return this;
        },
        getMessageContainer: function() {
           return this.form.closest('.pccf').find('.messages');
        },
        insertMessage: function(message, type) {
            var messageEm = this.generateMessageEm(message, type);
            this
                .getMessageContainer()
                .append(messageEm);
            messageEm.fadeTo(800, 1);
            return this;
        },
        generateMessageEm: function(message, type) {
            if(type == 'notice') type = 'info';
            if(type == 'error') type = 'danger';
            return $('<div />')
                    .html(message)
                    .css('opacity', 0)
                    .addClass('alert')
                    .addClass('alert-' + type);
        },
        scrollToMessageContainer: function() {
            var messageContainer = this.getMessageContainer();
            $('html, body').animate({
                scrollTop: messageContainer.offset().top - 80
            }, 'slow');
            return this;
        },
        isSuccessResponse: function(message) {
            if(
                typeof message == "object"
                && message != null
                && message.type == 'success'
            ){
                return true;
            }
            return false;
        },
        handleSubmitError: function(response) {
            this.form.trigger( "pccfFail", [this.form, this]);
            this.fadeInFormFieldsets();
            if(response.error_code == 'captcha')
            {
                this.refreshCaptcha();
            }
            return this;
        },
        refreshCaptcha: function() {
            if(this.hasVCaptcha()){
                this.form.closest('.pccf').data('vcaptcha').refresh();
            }
            return this;
        },
        fadeInFormFieldsets: function() {
            this.form.closest('.pccformwrapper')
                .css({
                    'max-height': 'auto',
                    'overflow': 'auto'
                });
            this.form.find('fieldset').fadeTo(400, 1);
            this.form.find('.nav-wizard').fadeTo(400, 1);
            return this;
        },
        fadeOutFormFieldsets: function() {
            var wrapper = this.form.closest('.pccformwrapper');
            wrapper
                .css({
                    'max-height': wrapper.innerHeight() + 'px',
                    'overflow': 'hidden'
                })
            this.form.find('fieldset').fadeTo(400, 0);
            this.form.find('.nav-wizard').fadeTo(400, 0);
            return this;
        },
        initDependentFields: function() {
            require(['PluginCompany_ContactForms/js/lib/dependsOn'], _.bind(function(){
                this
                    .addValueAttributeToSelectOptions()
                    .initDependencies();
                ;
            }, this));
            return this;
        },
        addValueAttributeToSelectOptions: function() {
            this.form.find('option').each(function(){
                if(!$(this).attr('value') && $(this).attr('value') != ""){
                    $(this).attr('value',$(this).text());
                }
            });
            return this;
        },
        initDependencies: function() {
            var self = this;
            var form = this.form;
            var dfields = this.config.dependentFields;
            var increment = this.uniqueIdentifier;
            $.each(dfields,function(fKey,fValue){
                var dependencies = {};
                $.each(fValue.dependencies,function(dKey,dValue){
                    switch(dValue.fieldType){
                        case 'dropdown':
                        case 'multiple':
                            var selector = '#' + dValue.field + increment;
                            var values = dValue.value;
                            values = [values];
                            break;
                        case 'text':
                            var selector = '#' + dValue.field + increment;
                            var values = dValue.value.split(';;');
                            break;
                        case 'checkboxes':
                            var selector = self.getDfieldSelectorForCheckboxOrRadio(dValue.field);
                            var values = dValue.value;
                            values = [values];
                            break;
                        case 'radios':
                            var selector = self.getDfieldSelectorForCheckboxOrRadio(dValue.field);
                            var values = dValue.value;
                            values = [values];
                            break;
                        default:
                            return true;
                    }
                    var deps = {};
                    deps[dValue.condition] = values;
                    deps['enabled'] = true;
                    dependencies[selector] = deps;
                });

                if(!$.isEmptyObject(dependencies)){
                    var em = form.find('label[for=' + fValue.name + increment + ']').parent();
                    if(!em.length){
                        em = form.find('#' + fValue.name + increment).closest('.row');
                    }
                    if(!em.length){
                        em = form.find('label[for=' + fValue.name + '_start' + increment + '], label[for=' + fValue.name + '_end' + increment + ']').parent();
                    }
                    em.dependsOn(dependencies,{
                        onEnable: function(event, subject){
                            subject
                                .find('input,select,textarea')
                                .prop('disabled', false)
                                .trigger("change")
                            ;
                            form.trigger( "dependentFieldChanged", [form, self]);
                        },
                        onDisable: function(event, subject){
                            subject
                                .find('input,select,textarea')
                                .prop('disabled', 'disabled')
                                .trigger("change")
                            ;
                            form.trigger( "dependendFieldChanged", [form, self]);
                        }
                    });
                }
            });
            return this;
        },
        getDfieldSelectorForCheckboxOrRadio: function(fieldId) {
            var field = $('#' + fieldId + '-0' + this.uniqueIdentifier);
            var className = fieldId + this.uniqueIdentifier;
            field
                .closest('.form-group')
                .addClass(className)
            ;
            return '.' + className + ' input';
        },
        initDateElements: function() {
            if(!this.hasDateFields()) return this;
            var self = this;
            require([
                'PluginCompany_ContactForms/js/lib/datetime/moment',
            ],function(moment){
                window.moment = moment;
                require([
                    'PluginCompany_ContactForms/js/lib/datetime/collapse-transitions.min',
                    'PluginCompany_ContactForms/js/lib/datetime/bootstrap-datetimepicker.min',
                    'css!PluginCompany_ContactForms/js/lib/datetime/bootstrap-datetimepicker.min',
                ],function(){
                    self.renderDateElements();
                })
            });
            return this;
        },
        hasDateFields: function() {
            return this.form.find('.date').length > 0;
        },
        renderDateElements: function() {
            var that = this;
            var form = this.form;

            var locale =  document.documentElement.lang.split('_')[0].split('-')[0].toLowerCase();
            //set global locale
            moment.locale(locale);
            locale = this.config.locale;
            //set sub locale like en-gb etc
            moment.locale(locale);
            locale = moment.locale();

            //date & time
            form.find('.date.dateandtime').each(function() {
                $(this).datetimepicker({
                    allowInputToggle: true,
                    sideBySide:true,
                    widgetPositioning: {horizontal:'right'},
                    locale: locale,
                    daysOfWeekDisabled: that.getDisabledDaysForDateElement(this),
                    minDate: that.getMinDateForDateElement(this)
                });
            })
            //only date
            form.find('.date.dateonly').each(function(){
                $(this).datetimepicker({
                    allowInputToggle: true,
                    format:'L',
                    locale: locale,
                    daysOfWeekDisabled: that.getDisabledDaysForDateElement(this),
                    minDate: that.getMinDateForDateElement(this)
                });
            });
            //only time
            form.find('.date.time').datetimepicker({
                format: 'LT',
                allowInputToggle: true,
                locale: locale
            });
            //date range
            form.find('.date.daterange').datetimepicker({
                allowInputToggle: true,
                format:'L',
                locale: locale
            });
            form.find('.date.daterange.datestart').on("dp.show", function (e) {
                var maxDateObj = $(e.target).parent().parent().next().find('.dateend').data("DateTimePicker");
                if(maxDateObj && maxDateObj.date()){
                    //set max date according to date end value
                    $(e.target).data('DateTimePicker').maxDate(maxDateObj.date());
                    //set min date according to max days parameter
                    var max_days = $(this).attr("max_days");
                    if (typeof max_days != "undefined" && max_days) {
                        $(e.target).data('DateTimePicker').minDate(maxDateObj.date().subtract(max_days, "day"));
                    }
                }
            });

            form.find('.date.daterange.dateend').on("dp.show", function (e) {
                var minDateObj = $(e.target).parent().parent().prev().find('.datestart').data("DateTimePicker");
                if(minDateObj && minDateObj.date()){
                    //set min date according to date start value
                    $(e.target).data('DateTimePicker').minDate(minDateObj.date());
                    //set max date according to max days parameter
                    var max_days = $(this).attr("max_days");
                    if (typeof max_days != "undefined" && max_days) {
                        $(e.target).data('DateTimePicker').maxDate(minDateObj.date().add(max_days, "day"));
                    }
                }
            })
            return this;
        },
        getDisabledDaysForDateElement: function(element) {
            var disabledDays = $(element).attr('data-disableddays');
            if(typeof disabledDays === "undefined") {
                return [];
            }
            return JSON.parse(disabledDays);
        },
        getMinDateForDateElement: function(element) {
            var minDate = $(element).attr('data-mindate');
            if(minDate == 'todayandlater') {
                return this.getTodayDate();
            }
            if(minDate == 'tomorrowandlater') {
                return this.getTomorrowDate();
            }
        },
        getTodayDate: function() {
            return moment();
        },
        getTomorrowDate: function() {
            return moment().add(1, 'days').toISOString().substr(0,10);
        },
        initUploadElements: function() {
            if(this.config.hasUploadField) {
                return this.initDropZoneElements();
            }
            return this;
        },
        initDropZoneElements: function() {
            require(
                [
                    'PluginCompany_ContactForms/js/lib/dropzone/dropzone-amd-module',
                    'css!PluginCompany_ContactForms/js/lib/dropzone/dropzone',
                ],
                _.bind(function(dropZone){
                    this.dropZone = dropZone;
                    this.renderDropZoneElements();
                }, this));

            return this;
        },
        renderDropZoneElements: function() {
            var uploadEm = this.form.find('.fs-upload-target');
            this.dropZoneEms = [];
            uploadEm.each(_.bind(function(k, em){
                $(em)
                    .attr('upload_text', $(em).text())
                    .text('')
                    .addClass('dropzone');
                var dropZone = new this.dropZone(em, this.getDropzoneConfig(uploadEm));
                this.dropZoneEms.push(dropZone);
            }, this))
            this.addDropZoneValidationFields();
            this.attachDropZoneEventListeners();
            return this;
        },
        getDropzoneConfig: function(em) {
            var maxFiles = em.parent().attr('max_files');
            var maxFileSize = em.parent().attr('max_filesize');
            if(maxFileSize > this.config.maxUploadSize){
                maxFileSize = this.config.maxUploadSize;
            }
            var allowedExt = em.parent().attr('allowed_ext');
            if(!allowedExt) {
                allowedExt = null;
            }else {
                allowedExt = '.' + allowedExt.replace(/\,/g,',.');
            }
            var uploadElementNumber = this.form.find('.fs-upload-target').index(em);
            var defaultMessage = em.attr('upload_text').trim();
            if(defaultMessage == 'Drag and drop files or click to select') {
                defaultMessage = $.mage.__('Drag and drop files or click to select');
            }
            return {
                url: this.config.uploadUrl + 'upload_element_index/' + uploadElementNumber + '/unique_form_instance/' + this.uniqueIdentifier,
                addRemoveLinks: true,
                maxFiles: maxFiles,
                maxFilesize: maxFileSize,
                acceptedFiles: allowedExt,
                dictDefaultMessage: defaultMessage,
                parallelUploads: 1,
                dictFallbackMessage: $.mage.__("Your browser does not support drag'n'drop file uploads."),
                dictFallbackText: $.mage.__("Please use the fallback form below to upload your files like in the olden days."),
                dictFileTooBig: $.mage.__("File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB."),
                dictInvalidFileType: $.mage.__("You can't upload files of this type."),
                dictResponseError: $.mage.__("Server responded with {{statusCode}} code."),
                dictCancelUpload: $.mage.__("Cancel upload"),
                dictUploadCanceled: $.mage.__("Upload canceled."),
                dictCancelUploadConfirmation: $.mage.__("Are you sure you want to cancel this upload?"),
                dictRemoveFile: $.mage.__("Remove file"),
                dictMaxFilesExceeded: $.mage.__("You can not upload any more files.")
            };
        },
        addDropZoneValidationFields: function() {
            $.each(this.dropZoneEms,function(k,dropZoneEm){
                var em = $(dropZoneEm['element']);
                if(!em.closest('.form-group').hasClass('required-control')){
                    return;
                }
                $('<input class="dropzone-validate required-entry" />')
                    .insertAfter(
                        $(dropZoneEm['element'])
                    );
            })
        },
        attachDropZoneEventListeners: function() {
            var self = this;
            $.each(this.dropZoneEms,function(k,dropZoneEm){
                dropZoneEm.on('removedfile', function(file){
                    self.removeUploadedFile(file);
                    self.updateDropZoneFileUploadCount();
                })
                dropZoneEm.on('success', function(file){
                    self.updateDropZoneFileUploadCount();
                })
            });
            return this;
        },
        removeUploadedFile: function(file) {
            var self = this;
            var fileName = file.name;
            $.ajax({
                showLoader: false,
                url: self.config.removeUrl,
                data: {filename: fileName, form_id: self.config.formId},
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                console.log(data);
            });
        },
        updateDropZoneFileUploadCount: function() {
            this.form.find('.dropzone-validate').each(function() {
                var uploads = $(this).closest('.pcc_upload').find('.dz-success').length;
                if(!uploads) uploads = '';
                $(this).val(uploads);
            })
        },
        initCaptcha: function() {
            if(this.hasReCaptcha()){
                this.initReCaptcha();
            }
            if(this.hasVCaptcha()){
                this.initVCaptcha();
            }
            return this;
        },
        hasReCaptcha: function() {
            return this.config.hasReCaptcha;
        },
        hasInvisibleReCaptcha: function() {
            return this.hasReCaptcha() && this.config.invisibleReCaptcha;
        },
        hasInvisibleReCaptchaNotInline: function() {
            return this.hasInvisibleReCaptcha() && this.getInvisibleReCaptchaPosition() != 'inline';
        },
        getInvisibleReCaptchaPosition: function() {
            return this.config.invisibleReCaptchaPosition;
        },
        initReCaptcha: function() {
            if(typeof grecaptcha == "undefined"){
                $('body').append("<script " + "src='https://www.google.com/recaptcha/api.js?render=explicit' async defer><\/script>");
            }
            var initReCaptcha = setInterval(
                function(){
                    if(typeof grecaptcha == "undefined" || typeof grecaptcha.render == "undefined") {
                        return;
                    }
                    clearInterval(initReCaptcha);
                    this.renderReCaptchaElements();
                }.bind(this),
                100
            )
            return this;
        },
        renderReCaptchaElements: function() {
            this.form.find('.g-recaptcha').each(_.bind(function (k, em) {
                try {
                    if(this.hasInvisibleReCaptcha()) {
                        $(em).before('<div class="g-recaptcha" data-size="invisible" data-badge="' + this.getInvisibleReCaptchaPosition() + '"/>');
                    }else{
                        $(em).before('<div class="g-recaptcha" />');
                    }
                    if(this.hasInvisibleReCaptchaNotInline()) {
                        $(em).closest('.form-group').addClass('recaptcha-not-inline');
                    }
                    var newCaptcha = $(em).prev();
                    $(em).remove();
                    this.recaptchaWidgetId = grecaptcha.render( newCaptcha[0], {
                            'sitekey': this.config.recaptchaKey,
                            'callback':  this.submitFormAfterInvisibleReCaptchaValidation
                        });
                } catch (err) {
                    console.log(err);
                }
            }, this));

            return this;
        },
        hasVCaptcha: function() {
            return this.config.hasVCaptcha;
        },
        initVCaptcha: function() {
            require(
                [
                    'PluginCompany_ContactForms/js/lib/visualcaptcha/js/visualcaptcha.jquery',
                    'css!PluginCompany_ContactForms/js/lib/visualcaptcha/css/visualcaptcha'
                ],
                _.bind(function() {
                    this.renderVisualCaptcha();
                }, this)
            )
            return this;
        },
        renderVisualCaptcha: function() {
            this
                .appendVCaptchaNamespaceEmToForm()
                .renderVisualCaptchaElement()
            ;
            return this;
        },
        appendVCaptchaNamespaceEmToForm: function() {
            this.form.append(this.getVCaptchaNameSpaceEm());
            return this;
        },
        getVCaptchaNameSpaceEm: function() {
            return '<input type="hidden" name="vcaptcha_namespace" value="' + this.uniqueIdentifier +'" />'
        },
        renderVisualCaptchaElement: function() {
            var config = this.config.visualCaptcha;
            var uniqId = this.uniqueIdentifier;
            var captchaEl = this.form.find('.vcaptcha')
                .visualCaptcha(
                    {
                        imgPath: config.imgUrl,
                        captcha: {
                            imgPath: config.imgUrl,
                            url: config.mainUrl,
                            numberOfImages: 5,
                            routes: {
                                start: '/start/vcaptcha_namespace/' + uniqId + '/count',
                                image: '/image/vcaptcha_namespace/' + uniqId + '/index',
                                audio: '/audio/vcaptcha_namespace/' + uniqId
                            }
                        }
                    }
            );
            var captcha = captchaEl.data( 'captcha' );
            this.form.closest('.pccf').data('vcaptcha', captcha);
            return this;
        },
        runPageloadJs: function() {
            this.config.pageloadJs();
            return this;
        }
    })

});
