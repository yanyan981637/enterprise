<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\TranslationPlus\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RemoveSchemaToAddNewColumns implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        // remove to add new columns: used_in_area, module, path_to_string
        $tableName =  $this->moduleDataSetup->getTable('mftranslation_index');

        if ($this->moduleDataSetup->getConnection()->isTableExists($tableName)) {
            $this->moduleDataSetup->getConnection()->dropTable(
                $this->moduleDataSetup->getTable('mftranslation_index')
            );
        }

        $this->moduleDataSetup->endSetup();
    }
}
