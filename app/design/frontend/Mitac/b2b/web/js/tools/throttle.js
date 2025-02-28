/**
 * 节流函数
 * 用于限制函数的执行频率,在指定时间内只执行一次
 * @param {Function} fn 需要被节流的函数
 * @param {Number} limit 节流时间间隔(毫秒)
 * @returns {Function} 返回节流后的函数
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (fn, limit) {
        let inThrottle = false; // 是否在节流时间内
        let lastArgs = null;    // 存储最后一次调用的参数
        return function (...args) {
            if (!inThrottle) {
                // 不在节流时间内,立即执行
                fn.apply(this, args)
                inThrottle = true
                
                // 设置定时器,limit时间后重置节流状态
                setTimeout(() => {
                    inThrottle = false
                    // 如果在节流期间有新的调用,执行最后一次调用
                    if(lastArgs){
                        fn.apply(this, lastArgs)
                        lastArgs = null
                    }
                }, limit)
            }else{
                // 在节流时间内,保存当前调用的参数
                lastArgs = args
            }
        };
    };
});
