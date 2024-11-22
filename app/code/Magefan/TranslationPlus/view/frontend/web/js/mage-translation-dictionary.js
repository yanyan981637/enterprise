/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define([
    'jquery',
    'text!js-translation.json'
], function ($, translation) {
    'use strict';
    translation = JSON.parse(translation);

    if (window.mfTranslationJson) {
        translation = $.extend(translation, window.mfTranslationJson);
    }
    return translation;
});
