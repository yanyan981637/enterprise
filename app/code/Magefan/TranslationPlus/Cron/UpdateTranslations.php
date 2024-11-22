<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Cron;

use Magefan\TranslationPlus\Controller\Adminhtml\Translation\Regenerate;

class UpdateTranslations
{
    /**
     * @var \Magefan\TranslationPlus\Model\TranslationIndex
     */
    private $translationIndex;

    /**
     * UpdateTranslations constructor.
     * @param \Magefan\TranslationPlus\Model\TranslationIndex $TranslationIndex
     */
    public function __construct(
        \Magefan\TranslationPlus\Model\TranslationIndex $TranslationIndex
    ) {
        $this->translationIndex = $TranslationIndex;
    }

    /**
     * @throws \Zend_Db_Exception
     */
    public function execute()
    {
        if ($this->translationIndex->getScheduleUpdate()) {
            $this->translationIndex->scheduleUpdate(false);
            $this->translationIndex->updateSchema();
            $this->translationIndex->updateData();
        }
    }
}
