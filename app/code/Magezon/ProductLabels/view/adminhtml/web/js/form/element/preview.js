/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imgUrl: '',
            styles: {},
            listens: {
                imgSrc: 'prepareImgUrl',
                value: 'setDifferedFromDefault loadImage'
            }
        },

        /**
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe('imgUrl styles');
            return this;
        },

        /**
         * Set imgUrl
         * @return string    
         */
        prepareImgUrl: function (url) {
            var imgSrc = '';
            if (url) {
                if (url.includes('http')) {
                    imgSrc = url;
                } else {
                    imgSrc = window.mgzMediaUrl + url;
                }
            }
            this.imgUrl(imgSrc);
        },

        /**
         * Set position img when click
         * @return string
         */
        loadImage: function () {
            if (this.value() >=3 && this.value() <=11) {
                var height      = $("#" + this.uid + "-preview").height();
                var width       = $("#" + this.uid + "-preview").width();
                var innerHeight = $("#" + this.uid + "-preview_inner").height();
                var innerWidth  = $("#" + this.uid + "-preview_inner").width();
                switch (this.value()) {
                  case '3':
                    this.styles({"top": 0, "left": 0, "right": "", "bottom": ""});
                    break;
                  case '4':
                    this.styles({"top": 0, "right": (width-innerWidth) / 2 + "px", "bottom": "", "left": ""});
                    break;
                  case '5':
                    this.styles({"top": "0", "right": "0", "bottom": "", "left": ""});
                    break;
                  case '6':
                    this.styles({"top": (height-innerHeight) / 2 + "px", "left": "0", "right": "", "bottom": ""});
                    break;
                  case '7':
                    this.styles({"top": (height-innerHeight) / 2 + "px", "right": (width-innerWidth) / 2 + "px", "bottom": "", "left": ""});
                    break;
                  case '8':
                    this.styles({"top": (height-innerHeight) / 2 + "px", "right": "0", "bottom": "", "left": ""});
                    break;
                  case '9':
                    this.styles({"bottom": "0", "left": "0", "top": "", "right": ""});
                    break;
                  case '10':
                    this.styles({"bottom": "0", "right": (width-innerWidth) / 2 + "px", "top": "", "left": ""});
                    break;
                  case '11':
                    this.styles({"bottom": 0, "right": "0", "top": "", "left": ""});
                    break;
                }
            } 
        }
    });
});