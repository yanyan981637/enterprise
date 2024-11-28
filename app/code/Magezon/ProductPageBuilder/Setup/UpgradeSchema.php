<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('mgz_productpagebuilder_profile'),
            'page_layout',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => 255,
                'comment' => 'Page Layout'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('mgz_productpagebuilder_profile'),
            'conditions_serialized',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => '64M',
                'nullable' => true,
                'comment'  => 'Conditions Serialized'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('mgz_productpagebuilder_profile'),
            'profile',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => '64M',
                'nullable' => true,
                'comment'  => 'Profile'
            ]
        );

        /**
         * Create table 'mgz_productpagebuilder_profile_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgz_productpagebuilder_profile_store')
        )->addColumn(
            'profile_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Profile ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('mgz_productpagebuilder_profile_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('mgz_productpagebuilder_profile_store', 'profile_id', 'mgz_productpagebuilder_profile', 'profile_id'),
            'profile_id',
            $installer->getTable('mgz_productpagebuilder_profile'),
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('mgz_productpagebuilder_profile_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'MGZ Product Page Builder Profile Store'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable($installer->getTable('mgz_productpagebuilder_profile_product'))
        ->addColumn(
            'profile_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Profile ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity'=> true, 'primary' => true],
            'Product ID'
        )->addIndex(
            $installer->getIdxName('mgz_productpagebuilder_profile_product', ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName(
                'mgz_productpagebuilder_profile_product', 
                'profile_id',
                'mgz_productpagebuilder_profile',
                'profile_id'
            ),
            'profile_id',
            $installer->getTable('mgz_productpagebuilder_profile'),
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mgz_productpagebuilder_profile_product', 
                'store_id', 
                'store', 
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mgz_productpagebuilder_profile_product', 
                'product_id', 
                'catalog_product_entity', 
                'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
        'MGZ Product Page Builder Profile Product'
        );
        $installer->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
