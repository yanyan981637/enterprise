define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/action/apply-filter',
], function ($, applyFilter) {
    'use strict';

    $.widget('mst.navHelperCheckedItems', {
        options: {
            count: 0,
            clearUrl: ''
        },

        _create: function () {
            if (!this.options.count) {
                return;
            }

            const counterWrapper = $('<div/>').addClass('mst-nav__checked-counter__wrapper');

            const counter = $('<div/>').addClass('mst-nav__checked-counter').text(this.options.count);
            const clearLink = $('<a/>');
            clearLink.attr('href', this.options.clearUrl);
            clearLink.attr('data-element', 'filter');
            clearLink.html('&#10005;');
            clearLink.on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                applyFilter.apply(this.options.clearUrl);
            }.bind(this))

            counterWrapper.append(counter).append(clearLink);

            const $title = $(this.element).closest('.filter-options-item').find('.filter-options-title');

            if ($('.mst-nav__checked-counter__wrapper', $title).length) {
                $('.mst-nav__checked-counter__wrapper', $title).remove();
            }

            $title.append(counterWrapper);
        },
    });

    return $.mst.navHelperCheckedItems;
});
