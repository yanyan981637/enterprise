<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model\Data;

use Magefan\TranslationPlus\Api\Data\TranslationIndexInterface;

class TranslationIndex extends \Magento\Framework\Api\AbstractExtensibleObject implements TranslationIndexInterface
{
    /**
     * Get TranslationIndex_id
     *
     * @return string|null
     */
    public function getTranslationIndexId()
    {
        return $this->_get(self::TranslationIndex_ID);
    }

    /**
     * Set TranslationIndex_id
     *
     * @param  string $TranslationIndexId
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface
     */
    public function setTranslationIndexId($TranslationIndexId)
    {
        return $this->setData(self::TranslationIndex_ID, $TranslationIndexId);
    }

    /**
     * Get dd
     *
     * @return string|null
     */
    public function getDd()
    {
        return $this->_get(self::DD);
    }

    /**
     * Set dd
     *
     * @param  string $dd
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface
     */
    public function setDd($dd)
    {
        return $this->setData(self::DD, $dd);
    }

    /**
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexExtensionInterface|\Magento\Framework\Api\ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @param  \Magefan\TranslationPlus\Api\Data\TranslationIndexExtensionInterface $extensionAttributes
     * @return TranslationIndex
     */
    public function setExtensionAttributes(
        \Magefan\TranslationPlus\Api\Data\TranslationIndexExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
