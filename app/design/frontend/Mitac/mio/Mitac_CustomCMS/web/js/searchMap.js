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

        $.widget('mage.searchMaps', {
            _create: function () {
                _.bindAll(this, '_onKeyDown', '_onPropertyChange');
                this.searchForm = $(this.options.formSelector);
                this.appendSelector = $(this.options.appendSelector);
                this.element.on('keydown', this._onKeyDown);
                this.element.on('input propertychange', this._onPropertyChange);
            },

            _onPropertyChange: function (e) {
                var filter, ul, li, a, i, txtValue;
                filter = this.element.val().toUpperCase();
                ul = this.appendSelector;
                li = ul.find("li");
                li.hide();
                if(filter){
                    for (i = 0; i < li.length; i++) {
                        a = li.eq(i).find("a");
                        txtValue = a.text();
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            li[i].style.display = "block";
                        } else {
                            li[i].style.display = "none";
                        }
                    }
                }
                e.preventDefault();
            },

            _onKeyDown: function (e) {
                var keyCode = e.keyCode || e.which;
                var li = this.appendSelector.find('li:visible');
                var value, selected = li.filter('.selected'),current;
                this.appendSelector.find('li').removeClass('selected');
                switch (keyCode) {
                    case $.ui.keyCode.ENTER:
                        if (!selected.length) {
                            current = li.eq(0);
                        }
                        li.hide();
                        this.searchForm.submit();
                        e.preventDefault();
                    break;
                    case $.ui.keyCode.DOWN:
                        if (!selected.length) {
                            current = li.eq(0);
                        } else {
                            current = selected.nextAll(':visible:first');
                        }
                        this.element.val(current.find('a').text());
                        e.preventDefault();
                    break;
                    case $.ui.keyCode.UP:
                        if (!selected.length) {
                            current = li.last();
                        } else {
                            current = selected.prevAll(':visible:first');
                        }
                        this.element.val(current.find('a').text());
                        e.preventDefault();
                    break;
                    case $.ui.keyCode.ESCAPE:
                        if (!selected.length) {
                            current = li.eq(0);
                        }

                        this.element.val(selected.find('a').text());
                        li.hide();
                    break;
                }
                if(current){
                    current.addClass('selected');
                }  
            }
        });

        return $.mage.searchMaps;
    });