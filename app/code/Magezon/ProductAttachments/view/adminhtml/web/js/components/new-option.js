define([
    'Magento_Ui/js/form/element/ui-select'
], function (Select) {
    'use strict';

    return Select.extend({

        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data);
            var copyOptionsTree;

            if (data.error) {
                return this;
            }

            var options = this.options();
            if (option.parent) {
                this.options([]);
                this.setOption(option);
                this.set('newOption', option);
            } else {
                this.cacheOptions.tree.push(option);
                this.cacheOptions.plain.push(option);
                this.options(this.cacheOptions.tree);
                this.value.push(option.value);
            }
        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            console.log(data);
            return {
                value: data.item['category_id'],
                label: data.item.name,
                level: data.item.level,
                parent: data.item.parent
            };
        }
    });
});
