define([
    'jquery',
    'underscore',
    'mage/translate'
], function ($, _, __) {
    "use strict";

    return {
        initProduct: function(ajaxUrl)
        {
            this.data = {
                isKeypress: false,
                estimationUrl: ajaxUrl,
                productId: $("input[type='hidden'][name='product_id']"),
                productType: $("input[type='hidden'][name='product_type']"),
                postCode: $("input[type='hidden'][name='post_code']"),
                countryCode: $("input[type='hidden'][name='country_code']"),
                allowedCountries: $("input[type='hidden'][name='has_allowed_countries']"),
                orderWithin: $("input[type='hidden'][name='order_within']"),
                displaySpinner: $("input[type='hidden'][name='display_spinner']"),
                containers: {
                    estimationWrapper: $('.estimation-wrapper'),
                    headingContainer: $('.heading-container'),
                    responseContainer: $('.response-container'),
                    loader: $('.estimation-wrapper #loader'),
                    date: $('.estimation-wrapper #date'),
                    prePostCode: $('.estimation-wrapper #pre_post_code'),
                    postCode: $('.estimation-wrapper #post_code'),
                    countryCode: $('.estimation-wrapper #country_code'),
                    estimationMessage: $('.estimation-wrapper #estimation_message'),
                    orderWithin: $('.estimation-wrapper #order_within'),
                    orderWithinContainer: $('.order-within-container'),
                    error: $('.estimation-wrapper #error')
                },
                inputs: {
                    postCodeInput: $("input[type='text'][name='visible_post_code']")
                }
            };

            if (this.data.displaySpinner.val() === '1') {
                this.data.containers.estimationWrapper.addClass('show-spinner');
            }

            var that = this;
            if (this.data.productType.val() === 'configurable') {
                this.waitUntil(function() {
                    return $('.swatch-attribute').length || $('select.super-attribute-select').length;
                }, function() {
                    that.data.options = $('.swatch-attribute, select.super-attribute-select');
                    that.data.optionsCount = $('.super-attribute-select').length;
                    that.data.options.on('change', function() {
                        that.observeOptionSelect();
                    });
                }, function() {
                    // do nothing
                });
            } else {
                this.estimateDelivery();
            }

            this.data.containers.postCode.add(this.data.containers.error).on('click', function() {
                that.updateNewLocation();
            });
            this.data.inputs.postCodeInput.on('blur', function() {
                if (that.data.isKeypress === false) {
                    that.estimateNewLocation();
                }
                that.data.isKeypress = false;
            });
            this.data.inputs.postCodeInput.on('keypress', function(e) {
                if (e.which === 13) {
                    that.data.isKeypress = true;
                    that.estimateNewLocation();
                }
            });

            return this;
        },
        waitUntil: function (isReady, success, error, count, interval){
            if (count === undefined) {
                count = 300;
            }
            if (interval === undefined) {
                interval = 20;
            }
            if (isReady()) {
                success();
                return;
            }
            var that = this;
            setTimeout(function(){
                if (!count) {
                    if (error !== undefined) {
                        error();
                    }
                } else {
                    that.waitUntil(isReady, success, error, count -1, interval);
                }
            }, interval);
        },
        observeOptionSelect: function()
        {
            var that = this,
                selectedCount = 0,
                delayCall = false;

            this.data.configurable = {};
            this.data.configurableRaw = {};
            this.data.options.each(function(i, option) {
                if (option.hasAttribute('data-option-selected')) {
                    selectedCount++;
                    that.data.configurableRaw[$(option).attr('data-attribute-id').toString()] =
                        $(option).attr('data-option-selected').toString();
                } else if ($(option).hasClass('super-attribute-select')) {
                    delayCall = true;
                    selectedCount++;
                    that.data.configurableRaw[$(option).attr('data-selector').replace(/[^0-9]/g, "")] =
                        $(option).val().toString();
                }
            });


            if (selectedCount === this.data.optionsCount) {
                if (delayCall) {
                    setTimeout(that.estimateDelivery.bind(that), 500);
                } else {
                    this.estimateDelivery();
                }
            }
        },
        estimateDelivery: function()
        {
            var that = this;
            $.ajax({
                method: "POST",
                global: false,
                cache: false,
                url: that.data.estimationUrl,
                data: {
                    allowed_countries: that.data.allowedCountries.val(),
                    product_type: that.data.productType.val(),
                    product_id: that.data.productId.val(),
                    postcode: that.data.postCode.val(),
                    country_code: that.data.countryCode.val(),
                    selected_product: that.getSimpleProductId()
                },
                dataType: "json",
                beforeSend: function() {
                    if (that.data.displaySpinner.val() === '1') {
                        that.data.containers.loader.addClass('visible');
                    }
                }
            }).done(function(response){
                that.hideContainers([
                    that.data.containers.error,
                    that.data.inputs.postCodeInput
                ]);
                that.data.containers.loader.removeClass('visible');
                that.data.containers.responseContainer.addClass('visible');
                that.data.containers.headingContainer.addClass('visible');
                if (response.success) {
                    that.setSuccessResponse(response);
                    that.showContainers([
                        that.data.containers.date,
                        that.data.containers.postCode,
                        that.data.containers.prePostCode,
                        that.data.containers.countryCode
                    ]);
                    if (response.estimate.hasOwnProperty('estimated_delivery_message')) {
                        that.showContainers([
                            that.data.containers.estimationMessage
                        ]);
                    }
                    if (response.estimate.time_remaining_seconds > 0 && response.estimate.time_remaining_seconds <= that.data.orderWithin.val()) {
                        that.showContainers([
                            that.data.containers.orderWithinContainer
                        ]);
                    }
                } else {
                    if (response.estimate.error !== '') {
                        that.hideContainers([
                            that.data.containers.date,
                            that.data.containers.postCode,
                            that.data.containers.prePostCode,
                            that.data.containers.countryCode,
                            that.data.containers.estimationMessage,
                            that.data.containers.orderWithinContainer
                        ]);
                        that.showContainers([that.data.containers.error]);
                        that.setErrorResponse(response);
                    } else {
                        that.data.containers.responseContainer.removeClass('visible');
                        that.data.containers.headingContainer.removeClass('visible');
                    }
                }
            });

            return this;
        },
        getSimpleProductId: function()
        {
            var that = this, selectedProductId = 0;
            if (this.data.productType.val() === 'configurable') {
                var swatchRendererOption = $('[data-role=swatch-options]').data('mageSwatchRenderer') || $('[data-role=swatch-options]').data('mage-SwatchRenderer');
                if (swatchRendererOption) {
                    var simpleProducts = swatchRendererOption.options.jsonConfig.index;
                    $.each(simpleProducts, function (productId, options) {
                        if (_.isEqual(options, that.data.configurableRaw)) {
                            selectedProductId = productId;
                        }
                    });
                } else {
                    selectedProductId = $("input[name=selected_configurable_option]").val();
                }
            }

            return selectedProductId;
        },
        setSuccessResponse: function(response)
        {
            // update text containers
            this.data.containers.date.text(response.estimate.estimated_delivery_date);
            this.data.containers.postCode.text(response.estimate.location_id);
            this.data.containers.countryCode.text(response.estimate.location_country);
            if (response.estimate.hasOwnProperty('estimated_delivery_message')) {
                this.data.containers.estimationMessage.text(response.estimate.estimated_delivery_message);
            }
            if (response.estimate.time_remaining_seconds > 0 && response.estimate.time_remaining_seconds <= this.data.orderWithin.val()) {
                this.data.containers.orderWithin.text(this.secondsToHMS(response.estimate.time_remaining_seconds));
            }
            // update inputs
            this.data.postCode.val(response.estimate.location_id);
            this.data.countryCode.val(response.estimate.location_country);
            this.data.inputs.postCodeInput.val(response.estimate.location_id);
        },
        setErrorResponse: function(response)
        {
            this.data.containers.error.html(response.estimate.error);
        },
        updateNewLocation: function()
        {
            this.hideContainers([
                this.data.containers.postCode,
                this.data.containers.error
            ]);
            this.showContainers([
                this.data.inputs.postCodeInput,
                this.data.containers.countryCode
            ]);
            this.data.inputs.postCodeInput.select();
        },
        estimateNewLocation: function()
        {
            this.data.inputs.postCodeInput.removeClass('empty');
            if (
                this.data.inputs.postCodeInput.val() !== '' &&
                this.data.inputs.postCodeInput.val() !== null
            ) {
                this.data.postCode.val(this.data.inputs.postCodeInput.val());
                this.estimateDelivery();
            } else {
                this.data.inputs.postCodeInput.addClass('empty');
            }
        },
        showContainers: function(elements)
        {
            elements.forEach(function(item) {
                item.show();
            });
        },
        hideContainers: function(elements)
        {
            elements.forEach(function(item) {
                item.hide();
            });
        },
        secondsToHMS: function(seconds)
        {
            var sec = parseInt(seconds, 10);

            var days = Math.floor(sec / (3600 * 24));
            sec  -= days * 3600 * 24;
            var hrs   = Math.floor(sec / 3600);
            sec  -= hrs * 3600;
            var mnts = Math.floor(sec / 60);
            sec  -= mnts * 60;

            var orderWithin  = days ? days > 1 ? days + ' day(s) ' : days + ' day ' : '';
                orderWithin += hrs  ? hrs > 1 ?  hrs + ' hrs ' : hrs + ' hr ' : '';
                orderWithin += mnts ? mnts > 1 ? mnts + ' mins ' :  mnts + ' min ' : '';

            return orderWithin;
        }
    };
});
