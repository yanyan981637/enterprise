<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Api\Data;

interface TranslationIndexInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Key of translation table
     */
    const DD = 'id';

    /**
     * Id table translation index
     */
    const TranslationIndex_ID = 'id';

    /**
     * Get TranslationIndex_id
     *
     * @return string|null
     */
    public function getTranslationIndexId();

    /**
     * Set TranslationIndex_id
     *
     * @param  string $TranslationIndexId
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface
     */
    public function setTranslationIndexId($TranslationIndexId);

    /**
     * Get dd
     *
     * @return string|null
     */
    public function getDd();

    /**
     * Set dd
     *
     * @param  string $dd
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface
     */
    public function setDd($dd);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param  \Magefan\TranslationPlus\Api\Data\TranslationIndexExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magefan\TranslationPlus\Api\Data\TranslationIndexExtensionInterface $extensionAttributes
    );
}
