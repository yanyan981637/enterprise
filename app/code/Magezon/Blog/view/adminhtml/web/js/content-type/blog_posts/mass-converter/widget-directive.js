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
    "Magento_PageBuilder/js/mass-converter/widget-directive-abstract",
    "Magento_PageBuilder/js/utils/object"
], function (_widgetDirectiveAbstract, _object) {
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */

    /**
     * @api
     */
    let WidgetDirective = /*#__PURE__*/function (_widgetDirectiveAbstr) {
        "use strict";

        _inheritsLoose(WidgetDirective, _widgetDirectiveAbstr);

        function WidgetDirective()
        {
            return _widgetDirectiveAbstr.apply(this, arguments) || this;
        }

        var _proto = WidgetDirective.prototype;

        /**
         * Convert value to internal format
         *
         * @param {object} data
         * @param {object} config
         * @returns {object}
         */
        _proto.fromDom = function fromDom(data, config)
        {
            let attributes = _widgetDirectiveAbstr.prototype.fromDom.call(this, data, config);

            data.appearance = attributes.appearance;
            data.title = attributes.title;
            data.list_layout = attributes.list_layout;
            data.grid_col = attributes.grid_col;
            data.custom_class = attributes.custom_class;
            data.limit = attributes.limit;
            data.categories = attributes.categories;
            data.featurepost = attributes.featurepost;
            data.post_ids = attributes.post_ids;
            data.padding = attributes.padding;
            data.disable_box_shadow = attributes.disable_box_shadow;
            data.disable_border = attributes.disable_border;
            data.post_author = attributes.post_author;
            data.post_date = attributes.post_date;
            data.post_cats = attributes.post_cats;
            data.post_comments = attributes.post_comments;
            data.post_views = attributes.post_views;
            data.read_time = attributes.read_time;
            data.post_excerpt = attributes.post_excerpt;
            data.owl_item_xl = attributes.owl_item_xl;
            data.owl_item_lg = attributes.owl_item_lg;
            data.owl_item_md = attributes.owl_item_md;
            data.owl_item_sm = attributes.owl_item_sm;
            data.owl_item_xs = attributes.owl_item_xs;
            data.owl_autoplay = attributes.owl_autoplay;
            data.owl_autoplay_hover_pause = attributes.owl_autoplay_hover_pause;
            data.owl_autoplay_timeout = attributes.owl_autoplay_timeout;
            data.owl_nav = attributes.owl_nav;
            data.owl_dots = attributes.owl_dots;
            data.owl_rtl = attributes.owl_rtl;
            data.owl_loop = attributes.owl_loop;
            data.owl_margin = attributes.owl_margin;

            return data;
        }

        /**
         * Convert value to knockout format
         *
         * @param {object} data
         * @param {object} config
         * @returns {object}
         */
        _proto.toDom = function toDom(data, config)
        {
            let attributes = {
                type: "Magezon\\Blog\\Block\\Widget\\PostList",
                anchor_text: "",
                id_path: "",
                show_pager: 0,
                is_page_builder: true, //Set Data listing is PageBuilder
                type_name: "Blog Posts PageBuilder"
            };

            if (data.appearance) {
                attributes.appearance = data.appearance;
            }
            if (data.title) {
                attributes.title = data.title;
            }

            if (data.list_layout) {
                attributes.list_layout = data.list_layout;
            }

            if (data.grid_col) {
                attributes.grid_col = data.grid_col;
            }

            if (data.custom_class) {
                attributes.custom_class = data.custom_class;
            }

            if (data.limit) {
                attributes.limit = data.limit;
            }

            if (data.categories) {
                attributes.categories = data.categories;
            }

            if (data.featurepost) {
                attributes.featurepost = data.featurepost;
            }

            if (data.post_ids) {
                attributes.post_ids = data.post_ids;
            }

            if (data.padding) {
                attributes.padding = data.padding;
            }

            if (data.disable_box_shadow) {
                attributes.disable_box_shadow = data.disable_box_shadow;
            }

            if (data.disable_border) {
                attributes.disable_border = data.disable_border;
            }

            if (data.post_author) {
                attributes.post_author = data.post_author;
            }

            if (data.post_date) {
                attributes.post_date = data.post_date;
            }

            if (data.post_cats) {
                attributes.post_cats = data.post_cats;
            }

            if (data.post_comments) {
                attributes.post_comments = data.post_comments;
            }

            if (data.post_views) {
                attributes.post_views = data.post_views;
            }

            if (data.read_time) {
                attributes.read_time = data.read_time;
            }

            if (data.post_excerpt) {
                attributes.post_excerpt = data.post_excerpt;
            }

            if (data.owl_item_xl) {
                attributes.owl_item_xl = data.owl_item_xl;
            }

            if (data.owl_item_lg) {
                attributes.owl_item_lg = data.owl_item_lg;
            }
            if (data.owl_item_md) {
                attributes.owl_item_md = data.owl_item_md;
            }

            if (data.owl_item_sm) {
                attributes.owl_item_sm = data.owl_item_sm;
            }

            if (data.owl_item_xs) {
                attributes.owl_item_xs = data.owl_item_xs;
            }

            if (data.owl_autoplay) {
                attributes.owl_autoplay = data.owl_autoplay;
            }

            if (data.owl_autoplay_hover_pause) {
                attributes.owl_autoplay_hover_pause = data.owl_autoplay_hover_pause;
            }
            if (data.owl_autoplay_timeout) {
                attributes.owl_autoplay_timeout = data.owl_autoplay_timeout;
            }

            if (data.owl_nav) {
                attributes.owl_nav = data.owl_nav;
            }

            if (data.owl_dots) {
                attributes.owl_dots = data.owl_dots;
            }

            if (data.owl_rtl) {
                attributes.owl_rtl = data.owl_rtl;
            }

            if (data.owl_loop) {
                attributes.owl_loop = data.owl_loop;
            }

            if (data.owl_margin) {
                attributes.owl_margin = data.owl_margin;
            }

            (0, _object.set)(data, config.html_variable, this.buildDirective(attributes));
            return data;
        }

        /**
         * @param {string} content
         * @returns {string}
         */
        _proto.encodeWysiwygCharacters = function encodeWysiwygCharacters(content)
        {
            return content.replace(/\{/g, "^[").replace(/\}/g, "^]").replace(/"/g, "`").replace(/\\/g, "|").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }

        /**
         * @param {string} content
         * @returns {string}
         */
        _proto.decodeWysiwygCharacters = function decodeWysiwygCharacters(content)
        {
            return content.replace(/\^\[/g, "{").replace(/\^\]/g, "}").replace(/`/g, "\"").replace(/\|/g, "\\").replace(/&lt;/g, "<").replace(/&gt;/g, ">");
        };

        return WidgetDirective;
    }(_widgetDirectiveAbstract);

    return WidgetDirective;
});
