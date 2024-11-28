/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'uiComponent'
], function ($, _, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            nameAttr: 'ello',
            src: '',
            style: ''
        },

        /**
         */
        initialize: function () {
            this._super();
            return this;
        }

    });
});
