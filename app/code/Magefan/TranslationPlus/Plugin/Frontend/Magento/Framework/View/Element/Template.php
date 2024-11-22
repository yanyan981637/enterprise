<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Plugin\Frontend\Magento\Framework\View\Element;

class Template
{
    /**
     * @param \Magento\Framework\View\Element\Template $subject
     * @param $result
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        \Magento\Framework\View\Element\Template $subject,
        $result
    ) {
        if ('require.js' == $subject->getNameInLayout()) {
            /* Create block using plagin to include it before the require.js config */
            $mfTranslationJson = $subject->getLayout()->createBlock(
                \Magefan\TranslationPlus\Block\TranslationJson::class
            )->toHtml();
            if ($mfTranslationJson) {
                $result = $mfTranslationJson . PHP_EOL . '        ' . $result;
            }
        }
        return $result;
    }
}
