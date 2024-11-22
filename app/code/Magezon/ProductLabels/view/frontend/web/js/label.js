define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('magezon.productlabels', {

	    _create: function () {
	    	this.initLoadLabel();
	    },

	    /**
	     * @return void
	     */
	    initLoadLabel: function () {
	    	const element = $(this.element);

	    	var self = this,
	    		itemParent = element.parents('.product-item'),
				target = itemParent.find(this.options.target),
				action = this.options.action,
				position = this.options.position;


			if (target.length) {
				element[action](target);
				element.css('display','block');
			} else {
				element.remove();
			}
			if (itemParent.find('.mgz-productlabels_wrapper').hasClass('has-img') || itemParent.find('.mgz-productlabels_wrapper').hasClass('has-content')) {
				itemParent.css("position", "relative");
			}

			$( window ).resize(function() {
				self.initGetScript(itemParent, position);
				element.css('display','block');
			}).resize();
	    },

	    /**
	     * @param  int position
	     * @return void
	     */
	    initGetScript: function(itemParent, position) {
	    	var element = $(this.element);
	    	if (position >= 3 && position <= 11) {
				var height      = itemParent.find('.product-image-container').height(),
					width       = itemParent.find('.product-image-container').width(),
					innerHeight = element.height(),
					innerWidth  = element.width();

		    	switch (position) {
	    			case 3:
						element.css("top", "0");
						element.css("right", "");
						element.css("bottom", "");
						element.css("left", "0");
					break;

					case 4:
						element.css("top", "0");
				        element.css("right", (width-innerWidth)/2);
				        element.css("bottom", "");
				        element.css("left", "");
					break;

					case 5:
						element.css("top", "0");
				        element.css("right", "0");
				        element.css("bottom", "");
				        element.css("left", "");
					break;

					case 6:
						element.css("top", (height-innerHeight)/2);
				        element.css("right", "");
				        element.css("bottom", "");
				        element.css("left", "0");
					break;

					case 7:
						element.css("top", (height-innerHeight)/2);
				        element.css("right", (width-innerWidth)/2);
				        element.css("bottom", "");
				        element.css("left", "");
					break;

					case 8:
						element.css("top", (height-innerHeight)/2);
				        element.css("right", "0");
				        element.css("bottom", "");
				        element.css("left", "");
					break;

					case 9:
						element.css("top", "");
				        element.css("right", "");
				        element.css("bottom", "0");
				        element.css("left", "0");
					break;

					case 10:
						element.css("top", "");
				        element.css("right", (width-innerWidth)/2);
				        element.css("bottom", "0");
				        element.css("left", "");
					break;

					case 11:
						element.css("top", "");
					    element.css("right", "0");
					    element.css("bottom", "0");
					    element.css("left", "");
					break;
		    	}

		    	if (element.width() == width) {
	                element.css("left", "0");
	                element.css("right", "0");
	            }

		    }
	    }
    });


    return $.magezon.productlabels;
});