function _inheritsLoose(subClass, superClass)
{
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    _setPrototypeOf(subClass, superClass);
}

function _setPrototypeOf(o, p)
{
    _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p)
    {
        o.__proto__ = p;
        return o;
    };
    return _setPrototypeOf(o, p);
}

define([
    "jquery",
    "knockout",
    "mage/translate",
    "Magento_PageBuilder/js/events",
    "slick",
    "underscore",
    "Magento_PageBuilder/js/config",
    "Magento_PageBuilder/js/content-type-menu/hide-show-option",
    "Magento_PageBuilder/js/content-type/preview"
], function (
    _jquery,
    _knockout,
    _translate,
    _events,
    _slick,
    _underscore,
    _config,
    _hideShowOption,
    _preview
) {
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */

    /**
     * @api
     */
    let Preview = /*#__PURE__*/function (_preview2) {
        "use strict";

        _inheritsLoose(Preview, _preview2);

        /**
         * Define keys which when changed should not trigger the slider to be rebuilt
         *
         * @type {string[]}
         */

        /**
         * @inheritdoc
         */
        function Preview(contentType, config, observableUpdater)
        {
            var _this;
            _this = _preview2.call(this, contentType, config, observableUpdater) || this;
            _this.displayPreview = _knockout.observable(false);
            _this.previewElement = _jquery.Deferred();
            _this.widgetUnsanitizedHtml = _knockout.observable();
            _this.slidesToShow = 5;
            _this.blogPostItemSelector = ".mgz-blog-posts";
            _this.centerModeClass = "center-mode";
            _this.messages = {
                EMPTY: (0, _translate)("Empty Posts"),
                NO_RESULTS: (0, _translate)("No posts were found matching your condition"),
                LOADING: (0, _translate)("Loading..."),
                UNKNOWN_ERROR: (0, _translate)("An unknown error occurred. Please try again.")
            };
            _this.ignoredKeysForBuild = ["margins_and_padding", "border", "border_color", "border_radius", "border_width", "css_classes", "text_align"];
            _this.placeholderText = _knockout.observable(_this.messages.EMPTY); // Redraw slider after content type gets redrawn

            _events.on("contentType:redrawAfter", function (args) {
                if (_this.element && _this.element.children) {
                    var $element = (0, _jquery)(_this.element.children);

                    if (args.element && $element.closest(args.element).length) {
                        $element.slick("setPosition");
                    }
                }
            });

            return _this;
        }

        /**
         * Return an array of options
         *
         * @returns {OptionsInterface}
         */
        var _proto = Preview.prototype;

        _proto.retrieveOptions = function retrieveOptions()
        {
            var options = _preview2.prototype.retrieveOptions.call(this);

            options.hideShow = new _hideShowOption({
                preview: this,
                icon: _hideShowOption.showIcon,
                title: _hideShowOption.showText,
                action: this.onOptionVisibilityToggle,
                classes: ["hide-show-content-type"],
                sort: 40
            });
            return options;
        }

        /**
         * On afterRender callback.
         *
         * @param {Element} element
         */
        _proto.onAfterRender = function onAfterRender(element)
        {
            this.element = element;
            this.previewElement.resolve(element);
        }

        /**
         * @inheritdoc
         */
        _proto.afterObservablesUpdated = function afterObservablesUpdated()
        {
            var _this2 = this;

            _preview2.prototype.afterObservablesUpdated.call(this);

            var data = this.contentType.dataStore.getState();

            if (this.hasDataChanged(this.previousData, data)) {
                this.displayPreview(false);

                var url = _config.getConfig("preview_url");

                var requestConfig = {
                    // Prevent caching
                    method: "POST",
                    data: {
                        role: this.config.name,
                        directive: this.data.main.html(),
                        type: 'blog_posts_page_builder'
                    }
                };
                this.placeholderText(this.messages.LOADING);

                _jquery.ajax(url, requestConfig).done(function (response) {
                    if (typeof response.data !== "object" || !Boolean(response.data.content)) {
                        _this2.placeholderText(_this2.messages.NO_RESULTS);
                        return;
                    }

                    if (response.data.error) {
                        _this2.widgetUnsanitizedHtml(response.data.error);
                    } else {
                        _this2.widgetUnsanitizedHtml(response.data.content);
                        _this2.displayPreview(true);
                    }

                    _this2.previewElement.done(function () {
                        (0, _jquery)(_this2.element).trigger("contentUpdated");
                    });
                }).fail(function () {
                    _this2.placeholderText(_this2.messages.UNKNOWN_ERROR);
                });
            }

            this.previousData = Object.assign({}, data);
        };

        /**
         * Determine if the data has changed, whilst ignoring certain keys which don't require a rebuild
         *
         * @param {DataObject} previousData
         * @param {DataObject} newData
         * @returns {boolean}
         */
        _proto.hasDataChanged = function hasDataChanged(previousData, newData)
        {
            previousData = _underscore.omit(previousData, this.ignoredKeysForBuild);
            newData = _underscore.omit(newData, this.ignoredKeysForBuild);
            return !_underscore.isEqual(previousData, newData);
        };

        return Preview;
    }(_preview);

    return Preview;
});
