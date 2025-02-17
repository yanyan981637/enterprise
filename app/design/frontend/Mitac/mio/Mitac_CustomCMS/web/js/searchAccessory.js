define([
    'jquery',
    'underscore',
    'mage/template',
    'matchMedia',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'mage/translate'
    ], function ($, _, mageTemplate, mediaCheck) {
        'use strict';

        $.widget('mage.searchAccessories', {
            _create: function () {
                _.bindAll(this, '_onPropertyChange');
                this.appendSelector = $(this.options.appendSelector);
                this.element.on('input propertychange', this._onPropertyChange);
            },

            _onPropertyChange: function (e) {
                var filter, ul, li, a, i, txtValue;
                filter = this.element.val().toUpperCase();
                ul = this.appendSelector;
                li = ul.find("li");
                if(filter){
                    for (i = 0; i < li.length; i++) {
                        a = li.eq(i).find(".product-name");
                        txtValue = a.text();
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            li[i].style.display = "inline-block";
                        } else {
                            li[i].style.display = "none";
                        }
                    }
                }else{
                    for (i = 0; i < li.length; i++) {
                        li[i].style.display = "inline-block";
                    }
                }
                e.preventDefault();
            },
        });

        return $.mage.searchAccessories;
    });