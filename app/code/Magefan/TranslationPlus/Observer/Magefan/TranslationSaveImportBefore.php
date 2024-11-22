<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Observer\Magefan;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TranslationSaveImportBefore implements ObserverInterface
{

    const TRANSLATION_TABLE_NAME = 'translation';

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $table = $observer->getData('table');
        if (0 === strpos($table, self::TRANSLATION_TABLE_NAME)) {
            $entityData = $observer->getData('entityData');
            $elements = $entityData->getData('elements');
            foreach ($elements as $translateItem => $translate) {
                foreach ($translate as $key => $item) {
                    if (empty($item['updated_at'])) {
                        $elements[$translateItem][$key]['updated_at'] = date('Y-m-d H:i:s');
                    }
                }
            }
            $entityData->setData('elements', $elements);
        }
    }
}
