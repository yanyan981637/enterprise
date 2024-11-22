define([
    'jquery',
    'underscore',
    'Mirasvit_LayeredNavigation/js/lib/qs',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/apply-button'
], function ($, _, qs, config, applyButton) {
    "use strict";

    return function (url, $initiator, force) {
        const actualParams = qs.parse(window.location.search.substr(1))
        let filtersParams = getFilters();

        if (actualParams['q']) {
            filtersParams['q'] = actualParams['q'];
        }

        let cacheKey = filtersParams.cacheKey !== undefined ? filtersParams.cacheKey : '';

        if (cacheKey) {
            delete filtersParams.cacheKey;
        }

        if (config.isSeoFilterEnabled()) {
            url = config.getFriendlyClearUrl();
        }

        const params = _.extend({}, filtersParams, {mode: 'by_button_click'});

        url = url.split('?')[0];
        const query = qs.stringify(params);

        if (query) {
            url += "?" + query;
        }

        applyButton.move($initiator);
        applyButton.show();

        cacheKey = url + cacheKey;

        applyButton.load(url, cacheKey, force);
    };

    function getFilters() {
        let filters = {};

        let cacheKey = '';

        _.each($('[data-mst-nav-filter]'), function (filter) {
            const $filter  = $(filter);
            let isSlider   = $filter.hasClass('mst-nav__slider');
            let filterName = $filter.attr('data-mst-nav-filter');
            filterName     = filterName.replace(/A\d{6,7}A/, '');

            cacheKey += $filter.attr('data-mst-nav-cache-key');

            let filterValues = [];

            if (isSlider) {
                let filterValue = getSliderPriceFilterValue($filter);
                if (filterValue) {
                    filterValues.push(filterValue);
                    filters[filterName] = filterValues.join(',');
                }
            } else {
                _.each($('[data-element = filter]._checked', $filter), function (item) {
                    let $item       = $(item);
                    let filterValue = $item.attr('data-value');

                    filterValues.push(filterValue);
                }.bind(this));

                if (filterValues.length > 0) {
                    filters[filterName] = filterValues.join(',');
                }
            }
        }.bind(this));

        filters['cacheKey'] = cacheKey;

        return filters;
    }

    function getSliderPriceFilterValue($el) {
        let priceWidget = $el.data("mst-navSliderRenderer");

        if (!priceWidget) {
            return false;
        }

        let minVal  = parseFloat(priceWidget.getMin()).toFixed(2);
        let maxVal  = parseFloat(priceWidget.getMax()).toFixed(2);
        let fromVal = parseFloat(priceWidget.from).toFixed(2);
        let toVal   = parseFloat(priceWidget.to).toFixed(2);

        if (fromVal === minVal && toVal === maxVal) {
            return false;
        }

        return fromVal + "-" + toVal;
    }
});
