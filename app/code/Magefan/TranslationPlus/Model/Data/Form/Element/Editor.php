<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model\Data\Form\Element;

class Editor extends \Magento\Framework\Data\Form\Element\Editor
{
    public function isEnabled()
    {
        return true;
    }
}
