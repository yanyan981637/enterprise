<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model\ResourceModel;

class TranslationIndex extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mftranslation_index', 'id');
    }

    /**
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return TranslationIndex
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($object->getAllStoreLocale() as $locale) {
            $locale = strtolower($locale);
            $object->setData(
                $locale . '_translated',
                ($object->getData($locale) != $object->getData('string')) ? 1 : 0
            );
        }
        return parent::_beforeSave($object);
    }
}
