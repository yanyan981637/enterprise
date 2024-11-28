/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define([
    'jquery',
    'text!js-translation.json',
    window.mfTranslationConfig ? ('text!' + (BASE_URL ? BASE_URL : window.location.origin + '/') + 'mftranslationplus/translations/get'
        + ( window.mfTranslationConfig.store_id ? ('?locale=' + window.mfTranslationConfig.locale
            + '&store_id=' + window.mfTranslationConfig.store_id
            + '&timestamp=' + window.mfTranslationConfig.timestamp) : '')
        ) : ''
], function ($, translation, additionaTranslation) {
    'use strict';

    if (!window.mfTranslationConfig) {
        return translation;
    }

    translation = JSON.parse(translation);
    additionaTranslation = JSON.parse(additionaTranslation);

    if (additionaTranslation) {
        translation = $.extend(translation, additionaTranslation);
    }
    return translation;
});

