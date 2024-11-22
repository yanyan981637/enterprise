<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Api\Data;

interface TranslationIndexSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get TranslationIndex list.
     *
     * @return \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface[]
     */
    public function getItems();

    /**
     * Set dd list.
     *
     * @param  \Magefan\TranslationPlus\Api\Data\TranslationIndexInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
