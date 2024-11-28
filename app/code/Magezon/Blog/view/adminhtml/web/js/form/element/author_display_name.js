define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
    	defaults: {
    		changed: false,
    		imports: {
                first_name: '${$.provider}:data.first_name',
                last_name: '${$.provider}:data.last_name',
                nickname: '${$.provider}:data.nickname'
            },
            listens: {
                first_name: 'updateOptions',
                last_name: 'updateOptions',
                nickname: 'updateOptions'
            },

            updateOptions: function() {
            	var options = [];

            	if (this.first_name) {
            		options.push({
            			value: 'f',
            			label: this.first_name
            		});
            	}

            	if (this.last_name) {
            		options.push({
            			value: 'l',
            			label: this.last_name
            		});
            	}

            	if (this.first_name || this.last_name) {
            		options.push({
            			value: 'fl',
            			label: this.first_name + ' ' + this.last_name
            		});
            	}

            	if (this.first_name || this.last_name) {
            		options.push({
            			value: 'lf',
            			label: this.last_name + ' ' + this.first_name
            		});
            	}

            	if (this.nickname) {
            		options.push({
            			value: 'n',
            			label: this.nickname
            		});
            	}
				this.options(options);
            }
    	}
    });
});