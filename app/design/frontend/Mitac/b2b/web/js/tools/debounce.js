/**
 * 防抖函數
 * @param {Function} fn - 需要防抖的函數
 * @param {number} delay - 延遲時間(毫秒)
 * @param {boolean} [immediate=false] - 是否立即執行
 * @returns {Function} - 返回防抖後的函數
 */

define([
    'jquery'
], function ($) {
    'use strict';
    return function(fn, delay, immediate = false) {
        let timer = null;

        return function (...args) {
            // 清除之前的定時器
            if (timer) clearTimeout(timer);

            // 是否立即執行
            if (immediate && !timer) {
                fn.apply(this, args);
            }

            // 設置新的定時器
            timer = setTimeout(() => {
                if (!immediate) {
                    fn.apply(this, args);
                }
                timer = null;
            }, delay);
        }
    }
})
