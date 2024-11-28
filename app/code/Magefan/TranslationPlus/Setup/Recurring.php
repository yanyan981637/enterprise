<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Setup;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Cache\Manager;

class Recurring implements InstallSchemaInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @param Config $config
     * @param Manager $cacheManager
     */
    public function __construct(
        Config $config,
        Manager $cacheManager
    ) {
        $this->config = $config;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        /*
        $this->config->saveConfig(
            \Magefan\TranslationPlus\Model\Config::XML_PATH_LAST_STATIC_CONTENT_DEPLOY_DATETIME,
            date('Y-m-d H:i:s')
        );
        $this->cacheManager->clean(['config']);
        */
    }
}
