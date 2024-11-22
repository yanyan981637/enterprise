define([
    'jquery',
    './loader'
], function ($, loader) {
    "use strict";

    $.widget('mst.ajaxScroll', {
        nextBtn:          null,
        prevBtn:          null,
        isActive:         false,
        excludeHeight:    null,
        dataPageAttr:     'scroll-page',
        currLimit:        null,
        modeSwitched:     false,
        maxLoadedPageNum: 0,
        minLoadedPageNum: 1000,
        progressBar:      null,
        loadedPages:      1,

        modes: {
            infinite:        '_initInfiniteMode',
            button:          '_initButtonMode',
            infinite_button: '_initInfiniteButtonMode',
            button_infinite: '_initButtonInfiniteMode'
        },

        options: {
            mode:                       'button',
            pageLimit:                  0,
            moreBtnClass:               'mst-scroll__button', // "load more" buttons class
            postCatalogHeightSelectors: [
                '.main .products ~ .block-static-block',
                '.page-footer',
                '.page-bottom'
            ],
            // elements that should be hidden
            hide:                       [
                '.pages',
                '.toolbar-amount'
            ],
            // initial info
            factor:                     0.95, // factor for loading next page when scrolling down in infinite mode
            pageParam:                  'p',
            pageNum:                    1,
            initPageNum:                1,
            prevPageNum:                null,
            nextPageNum:                null,
            lastPageNum:                null,
            loadPrevText:               'Load Previous Page',
            loadNextText:               'Load More',
            progressBarEnabled:         1,
            progressBarText:            'Loaded %loaded% of %total% items',
            itemsTotal:                 100000,
            itemsLimit:                 12
        },

        _create: function () {
            // scroll is active only when catalog has more than 1 page
            this.isActive = this.options.nextPageNum || this.options.prevPageNum || false;

            // set initial page number to product list
            this.element.data(this.dataPageAttr, this.options.pageNum);

            // hide default DOM elements such as pagination
            this._hideElements();

            // init scroll widget in chosen mode
            this[this.modes[this.options.mode]]();

            this._bind();

            this.progressBar = this._createProgressBar();

            this._updateLabels(this.options);
        },

        _destroy: function () {
            this.isActive = false;
            this.options = {};
        },

        /**
         * Bind scroll event and load products when window is scrolled down.
         */
        _initInfiniteMode: function () {
            this._initScroll();

            // init button for previous page
            if (this.options.prevPageNum) {
                this.prevBtn     = this._createButton(this.options.loadPrevText, this.options.prevPageNum, 'insertBefore');
                this.progressBar = this._createProgressBar();
            }
        },

        _initScroll: function () {
            var onPause = false;

            $(window).scroll(function () {
                var scrollTop = $(window).scrollTop();

                if (scrollTop >= this._calculateHeightDiff() && !onPause && this.options.nextPageNum) {
                    this.currLimit--;

                    // stop processing infinite scroll when page limit reached
                    if (this.options.mode === 'infinite_button' && this.currLimit < 0) {
                        return;
                    }
                    onPause = true; // suspend ajax scrolling

                    loader.show(this._getLoadedPageSelector('last'));

                    this._request({p: this.options.nextPageNum})
                        .done(loader.hide.bind(loader))
                        .done(this._updateCatalog.bind(this))
                        .done(function (response) { // update next page num
                            if (response.config) {
                                this.options.nextPageNum = response.config.nextPageNum;
                                onPause = false; // resume ajax scrolling
                            }
                        }.bind(this));
                }
            }.bind(this));
        },

        _getLoadedPageSelector: function (type) {
            return this._getProductListSelector()
                .split(',')
                .map(function (item) {
                    return item + ':' + type;
                })
                .join(',');
        },

        /**
         * Calculate difference between the whole document height and its visible part + height of excluded blocks.
         *
         * @return {Number}
         */
        _calculateHeightDiff: function () {
            var diff = $(this._getLoadedPageSelector('last')).height()
                + $(this._getLoadedPageSelector('last')).offset().top
                - $(window).height();

            diff -= this._getExcludeHeight();

            return diff;
        },

        /**
         * Initialize widget in button mode.
         */
        _initButtonMode: function () {
            this._initButtons();
        },

        _initInfiniteButtonMode: function() {
            this.currLimit = this.currLimit === null ? this.options.pageLimit : this.currLimit;

            this._initInfiniteMode();
        },

        _initButtonInfiniteMode: function() {
            this.currLimit = this.currLimit === null ? this.options.pageLimit : this.currLimit;

            this._initButtonMode();
        },

        /**
         * Create buttons.
         */
        _initButtons: function () {
            if (this.options.nextPageNum) {
                this.nextBtn = this._createButton(this.options.loadNextText, this.options.nextPageNum, 'insertAfter');
            }

            if (this.options.prevPageNum) {
                this.prevBtn = this._createButton(this.options.loadPrevText, this.options.prevPageNum, 'insertBefore');
            }
        },

        /**
         * Create html button and attach it to widget's element.
         *
         * @param {String} label
         * @param {Number} pageNum - number of page used for button
         * @param {String} method - method used to insert the button over widget's element
         *
         * @return {jQuery}
         */
        _createButton: function (label, pageNum, method) {
            var element = method === 'insertAfter' ? $(this._getLoadedPageSelector('last')) : $(this._getLoadedPageSelector('first'));
            var btnClass = method === 'insertAfter' ? "_next" : "_prev";

            if ($('.mst-scroll__button.' + btnClass).length) {
                return $('.mst-scroll__button.' + btnClass);
            }

            return $('<button class="action primary"></button>')
                .text(label)
                .data('page', pageNum)
                .addClass(this.options.moreBtnClass)
                .addClass(btnClass)
                [method](element);
        },

        _createProgressBar: function () {
            if (!this.options.progressBarEnabled) {
                return null;
            }

            if ($('.mst-scroll__progress').length) {
                return $('.mst-scroll__progress');
            }

            if (this.progressBar) {
                return this.progressBar;
            }

            let progressBar = $(
                '<div class="mst-scroll__progress">' +
                '<span class="mst-scroll__progress-label"></span>' +
                '<div class="mst-scroll__progress-bar"><div></div></div>' +
                '</div>'
            );

            let selector = '';

            if (this.nextBtn) {
                selector = 'button.' + this.options.moreBtnClass + '._next';
            } else {
                selector = this._getLoadedPageSelector('last') + ' ~ *:first';
            }

            if (this.prevBtn) {
                selector += ', ' + 'button.' + this.options.moreBtnClass + '._prev';
            } else {
                selector += ', ' + this._getLoadedPageSelector('first');
            }

            progressBar.insertBefore($(selector));

            return progressBar;
        },

        /**
         * Hide DOM elements listed in this.options.hide array.
         */
        _hideElements: function () {
            this.options.hide.map(function (selector) {
                // hide only if "load" buttons exist
                if (this.isActive) {
                    // mark all hidden elements with our identification
                    $(selector + ':visible').data('scroll', 'hidden').hide();
                } else {
                    // show only elements hidden by us
                    $(selector).find('[data-scroll="hidden"]').show();
                }
            }.bind(this));
        },

        _bind: function () {
            var self = this;

            // observe "load more" buttons clicks
            $('.' + this.options.moreBtnClass).on('click', function () {
                var page      = $(this).data('page'),
                    targetBtn = page > self.options.initPageNum ? self.nextBtn : self.prevBtn;

                self.currLimit--;

                // stop processing nextBtn when page limit reached
                if (self.options.mode === 'button_infinite' && self.currLimit < 0 && targetBtn.hasClass('_next')) {
                    return;
                }

                targetBtn.addClass('_loading');
                targetBtn.attr('disabled', true);

                self._request({p: page})
                    .done(function () {
                        targetBtn.removeClass('_loading');
                    })
                    .done(self._updatePaging.bind(self))
                    .done(self._updateCatalog.bind(self))
                    .done(() => targetBtn.attr('disabled', false));

                ;
            });

            // update URL when scrolling through pages
            $(window).scroll(function () {
                if (this.isActive) {
                    this._updateHistory({config: {pageNum: this._determineCurrentPage()}});
                }
            }.bind(this));
        },

        /**
         * Determine current page by scrollTop position.
         *
         * @return {Number} - current page number
         */
        _determineCurrentPage: function () {
            var page        = null,
                self        = this,
                biggestPart = 0;

            $(this._getProductListSelector()).each(function () {
                var $list       = $(this),
                    $window     = $(window),
                    visiblePart = 0; // visible part of a product list block in window

                if ($list.offset().top - $window.scrollTop() < 0) { // list block is above window
                    visiblePart = $list.offset().top + $list.height() - $window.scrollTop();
                } else {
                    visiblePart = $window.height() - ($list.offset().top - $window.scrollTop());
                }

                if (visiblePart < 0) {
                    return; // skip current product list and continue loop
                }

                // if whole product list completely fit on the window
                if (visiblePart >= $list.height()
                    // or the product list takes up most of the window size
                    || $list.height() > $window.height() && $window.height() / visiblePart < 2
                ) {
                    page = $list.data(self.dataPageAttr) || 1;
                    return false; // we found page, stop looping
                }

                // otherwise use the page that takes up the most part of a space in the window
                if (visiblePart > biggestPart) {
                    biggestPart = visiblePart;
                    page = $list.data(self.dataPageAttr) || 1;
                }
            });

            return page;
        },

        _updateLabels: function (config) {
            const btnPattern    = "%limit%";
            const totalPattern  = '%total%';
            const loadedPattern = '%loaded%';

            if (this.prevBtn) {
                this.prevBtn.text(config.loadPrevText.replace(btnPattern, config.itemsLimit));
            }

            let nextLimit = config.itemsLimit;

            if ((config.pageNum + 1) * config.itemsLimit > config.itemsTotal) {
                nextLimit = config.itemsTotal - config.pageNum * config.itemsLimit;
            }

            if (this.nextBtn) {
                this.nextBtn.text(config.loadNextText.replace(btnPattern, nextLimit));
            }

            if (this.progressBar) {
                let progressBarLabel = config.progressBarText;
                let initPageItems    = this.options.initPageNum * this.options.itemsLimit > this.options.itemsTotal
                    ? this.options.itemsTotal - (this.options.initPageNum - 1) * this.options.itemsLimit
                    : this.options.itemsLimit;

                let calculatedLoadedItems = initPageItems + (this.loadedPages - 1) * config.itemsLimit;

                let current = calculatedLoadedItems > config.itemsTotal
                    ? config.itemsTotal
                    : calculatedLoadedItems;

                progressBarLabel = progressBarLabel.replace(totalPattern, config.itemsTotal)
                    .replace(loadedPattern, current);

                $('.mst-scroll__progress-label').text(progressBarLabel);

                let width = Math.round(current / config.itemsTotal * 100) + '%';

                $('.mst-scroll__progress-bar div').width(width);
            }

            this.loadedPages++;
        },

        /**
         * Update catalog products.
         *
         * @param {Object} response
         */
        _updateCatalog: function (response) {
            var selector      = this._getProductListSelector(),
                productList   = null,
                targetWrapper = null,
                doc = document.documentElement,
                top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);

            if (response.products && response.config) {
                // wrap product list into div to be able to retrieve needed selector
                productList = $('<div/>').html(response.products).find(selector);

                var $wrapper = $('<div/>').html(response.products);
                $wrapper.find(selector).remove();
                //left js outside productList (required for init the ajax cart)
                var scripts = $wrapper.find('script');

                productList.data(this.dataPageAttr, response.config.pageNum);
                // insert products after last of first list accordingly
                if (response.config.pageNum > this.options.initPageNum) {
                    targetWrapper = $(selector).last();
                    productList.insertAfter(targetWrapper);
                    window.scroll(0, top);
                } else {
                    targetWrapper = $(selector).first();
                    productList.insertBefore(targetWrapper);
                }

                scripts.insertAfter(targetWrapper);
                window.scroll(0, top);

                // trigger 3rd party events
                targetWrapper.trigger('contentUpdated');

                $(this._getProductListSelector()).trigger('contentUpdated');
                $(this._getProductListSelector()).each(function (idx, item) {
                    try { // to prevent error "You cannot apply bindings multiple times to the same element"
                        $(item).applyBindings();
                    } catch (e) {

                    }
                });

                setTimeout(function () {
                    // execute after swatches are loaded
                    $(document).trigger('amscroll_refresh');
                }, 500);
                if ($.fn.lazyload) {
                    // lazyload images for new content (Smartwave_Porto theme)
                    $('.porto-lazyload').lazyload({
                        effect: 'fadeIn'
                    });
                }

                if ($('.lazyload').length) {
                    try {
                        $("img.lazyload").unveil(0, function () {
                            $(this).load(function () {
                                this.classList.remove("lazyload");
                            });
                        });
                    } catch (e) {}
                }

                $(document).trigger('contentUpdated');

                // update form_key
                let formKey = $.mage.cookies.get('form_key');

                $('input[name="form_key"]', $(this._getProductListSelector())).each(function (idx, elem) {
                    const $elem = $(elem);
                    if (!formKey) {
                        formKey = $elem.val();
                    }

                    if ($elem.val() !== formKey) {
                        $elem.val(formKey);
                    }
                });

                // switch infinite mode to button mode
                if (this.options.mode === 'infinite_button' && this.currLimit <= 0 && !this.modeSwitched) {
                    if (this.options.nextPageNum) {
                        this.options.nextPageNum++;

                        if (this.options.lastPageNum >= this.options.nextPageNum) {
                            this.nextBtn     = this._createButton(this.options.loadNextText, this.options.nextPageNum, 'insertAfter');
                            this.progressBar = this._createProgressBar();
                            this._bind();
                        }
                    }

                    this.modeSwitched = true;
                }

                // switch button mode to infinite mode
                if (this.options.mode === 'button_infinite' && this.currLimit <= 0 && !this.modeSwitched) {
                    if (this.options.nextPageNum) {
                        this.options.nextPageNum += 2;

                        if (this.options.lastPageNum >= this.options.nextPageNum) {
                            this._initScroll();
                            this._bind();
                        }
                    }

                    $('.mst-scroll__button._next').remove();

                    this.modeSwitched = true;
                }

                this._updateLabels(response.config);
            }
        },

        /**
         * @return {String}
         */
        _getProductListSelector: function () {
            var selector = this.options.productListSelector;

            if ($(selector).length) {
                return selector;
            }

            selector = '.' + this.element.attr('class').split(' ').filter(Boolean).join('.');

            if (!$(selector).length) {
                if (selector.indexOf('grid') >= 0) {
                    selector = selector.replace(/grid/ig, 'list');
                } else {
                    selector = selector.replace(/list/ig, 'grid');
                }
            }

            return selector;
        },

        /**
         * Update paging buttons.
         *
         * @param {Object} response
         */
        _updatePaging: function (response) {
            // hide next/prev buttons
            if (response.config) {
                // if next page was loaded - change next page number, otherwise change prev page number
                if (response.config.pageNum > this.options.initPageNum) {
                    this.nextBtn.data('page', response.config.nextPageNum);
                } else {
                    this.prevBtn.data('page', response.config.prevPageNum);
                }

                // for hiding nextBtn if last page is loaded and prevBtn clicked (initial page != 1)
                this.maxLoadedPageNum = this.maxLoadedPageNum > response.config.pageNum
                    ? this.maxLoadedPageNum
                    : response.config.pageNum;

                // for hiding prevBtn if first page is loaded and nextBtn clicked (initial page != 1)
                this.minLoadedPageNum = this.minLoadedPageNum < response.config.pageNum
                    ? this.minLoadedPageNum
                    : response.config.pageNum;

                // hide next/prev page buttons if first or last pages loaded
                if (response.config.pageNum === 1 || this.minLoadedPageNum === 1) {
                    this.prevBtn.hide();
                } else if (this.prevBtn) {
                    this.prevBtn.show();
                }

                if (response.config.pageNum === response.config.lastPageNum || this.maxLoadedPageNum === response.config.lastPageNum) {
                    this.nextBtn.hide();
                } else if (this.nextBtn) {
                    this.nextBtn.show();
                }
            }
        },

        /**
         * Update page number param in URL.
         *
         * @param {Object} response
         */
        _updateHistory: function (response) {
            var url            = null,
                currentPageNum = null;

            if (response.config) {
                url = this._getUrl(response.config.pageNum);
                currentPageNum = this._getUrl().searchParams.get(this.options.pageParam);

                // ignore page #1
                if (response.config.pageNum === 1 && currentPageNum === null) {
                    return;
                }

                if (parseInt(currentPageNum) !== parseInt(response.config.pageNum)) {
                    history.replaceState({}, document.title, url.href);
                }
            }
        },

        /**
         * Send XHR.
         *
         * @param {String} url
         * @param {Object} data
         *
         * @return {Object}
         */
        _request: function (data) {
            data.is_scroll = 1;

            let url   = window.location.origin + window.location.pathname;
            let query = new URLSearchParams(window.location.search);

            if (query.has('p')) {
                query.delete('p');
            }

            if (query.toString()) {
                url += '?' + query.toString();
            }

            return $.ajax({
                url:   url,
                data:  data,
                cache: true
                //showLoader: true
            });
        },

        _getExcludeHeight: function () {
            var height = 0;

            if (this.excludeHeight === null) {
                if (!this.options.postCatalogHeightSelectors) {
                    this.options.postCatalogHeightSelectors = [
                        '.main .products ~ .block-static-block',
                        '.page-footer',
                        '.page-bottom'
                    ];
                }

                this.options.postCatalogHeightSelectors.map(function (selector) {
                    var block = $(selector);

                    if (block.length) {
                        height += block.first().height();
                    }
                });

                this.excludeHeight = height;
            }

            return this.excludeHeight;
        },

        /**
         * Get the URL for fetching additional products.
         *
         * @param {Number|Null} pageNum
         *
         * @return {URL}
         */
        _getUrl: function (pageNum) {
            var url = new URL(window.location);

            if (pageNum) {
                if (parseInt(pageNum) === 1) {
                    url.searchParams.delete(this.options.pageParam);
                } else {
                    url.searchParams.set(this.options.pageParam, pageNum);
                }
            }

            return url;
        }
    });

    return $.mst.ajaxScroll;
});
