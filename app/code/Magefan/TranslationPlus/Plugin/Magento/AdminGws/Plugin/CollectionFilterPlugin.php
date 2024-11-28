<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Plugin\Magento\AdminGws\Plugin;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magefan\TranslationPlus\Model\Config;

class CollectionFilterPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * CollectionFilterPlugin constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param mixed $subject
     * @param callable $proceed
     * @param AbstractCollection $collection
     * @param $printQuery
     * @param $logQuery
     * @return array|false[]
     */
    public function aroundBeforeLoadWithFilter(
        $subject,
        callable $proceed,
        AbstractCollection $collection,
        $printQuery = false,
        $logQuery = false
    )
    {
        if (false !== strpos(get_class($collection), 'Magefan\Translation') && $this->config->isIgnoreGWSPermissions()) {
            return [$printQuery, $logQuery];
        }

        return $proceed($collection, $printQuery, $logQuery);
    }

    /**
     * @param mixed $subject
     * @param callable $proceed
     * @param AbstractCollection $collection
     * @return void
     */
    public function aroundBeforeGetSelectCountSql(
        $subject,
        callable $proceed,
        AbstractCollection $collection
    )
    {
        if (false !== strpos(get_class($collection), 'Magefan\Translation') && $this->config->isIgnoreGWSPermissions()) {
            return;
        }
        return $proceed($collection);
    }

}
