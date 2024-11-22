<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $translationTable = $setup->getConnection()->getTableName($setup->getTable('translation'));

        $query = 'ALTER TABLE ' . $translationTable . ' DROP INDEX `TRANSLATION_STORE_ID_LOCALE_CRC_STRING_STRING`,
        ADD UNIQUE `MYM2TRANSLATION_STORE_ID_LOCALE_CRC_STRING_STRING` (`store_id`, `locale`, `crc_string`) USING BTREE;';

        try {
            $installer->getConnection()->query($query);
        } catch (\Exception $e) {
            /* Do nothing. Changed by db_schema.xml */
        }


        $setup->getConnection()->modifyColumn(
            $setup->getTable($translationTable),
            'string',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => '64k',
                'nullable' => false,
                'comment'  => 'Translation String'
            ]
        );

        $setup->getConnection()->modifyColumn(
            $setup->getTable($translationTable),
            'translate',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => '64k',
                'nullable' => false,
                'comment'  => 'Translate',
            ]
        );
        $setup->endSetup();

        $installer->endSetup();
    }
}
