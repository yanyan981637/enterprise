define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiRegistry',
    'underscore',
    'Magento_Ui/js/form/components/insert-listing'
], function ($, registry, _, InsertListing) {
    'use strict';

    return InsertListing.extend({
        defaults: {},

        /**
         * Render attribute
         */
        render: function () {
            if(typeof this.form_id == "undefined"){
                this.form_id = -1;
            }
            this._super({form_id: this.form_id});
        },

        /**
         * Save attribute
         */
        save: function () {
            this._super();
        }

    });
});
