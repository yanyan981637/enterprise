/**
 * Component implements overlay logic for layered navigation.
 */
define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/lib/nprogress'
], function ($, nprogress) {
    'use strict';

    const className = 'navigation-overlay';
    const $overlay = $('<div><i class="fa fa-spinner fa-spin"></i></div>').addClass(className);

    $('.columns').append($overlay);

    return {
        show: function () {
            $overlay.show()
            nprogress.start()

            setTimeout(function () {
                $overlay.addClass('_show')
            }, 10)
        },

        hide: function () {
            $overlay.hide().removeClass('_show')

            nprogress.done()
        }
    };
});
