<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

	    /**
	     * Create table 'mgz_productlabels_label'
	     */
	    $table = $installer->getConnection()->newTable(
	        $installer->getTable('mgz_productlabels_label')
	    )->addColumn(
	        'label_id',
	        Table::TYPE_SMALLINT,
	        null,
	        ['identity' => true, 'nullable' => false, 'primary' => true],
	        'Tab ID'
	    )->addColumn(
	        'name',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'Tab Name'
	    )->addColumn(
	        'priority',
	        Table::TYPE_INTEGER,
	        11,
	        ['nullable' => true],
	        'Priority'
	    )->addColumn(
	        'creation_time',
	        Table::TYPE_TIMESTAMP,
	        null,
	        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
	        'Tab Creation Time'
	    )->addColumn(
	        'update_time',
	        Table::TYPE_TIMESTAMP,
	        null,
	        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
	        'Tab Modification Time'
	    )->addColumn(
            'from_date',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Label From Date'
        )->addColumn(
	        'productpage_image',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductPage Image'
	    )->addColumn(
	        'productpage_html',
	        Table::TYPE_TEXT,
            '2M',
	        ['nullable' => false],
	        'ProductPage HTML'
	    )->addColumn(
	        'productpage_position',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductPage Position'
	    )->addColumn(
	        'productpage_color',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductPage Color'
	    )->addColumn(
	        'productpage_width',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductPage Width'
	    )->addColumn(
	        'productpage_style',
	        Table::TYPE_TEXT,
            '2M',
	        ['nullable' => false],
	        'ProductPage Style'
	    )->addColumn(
	        'productpage_url',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductPage Url'
	    )->addColumn(
	        'productlist_image',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductList Image'
	    )->addColumn(
	        'productlist_html',
	        Table::TYPE_TEXT,
            '2M',
	        ['nullable' => false],
	        'ProductList HTML'
	    )->addColumn(
	        'productlist_position',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductList Position'
	    )->addColumn(
	        'productlist_color',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductList Color'
	    )->addColumn(
	        'productlist_width',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductList Width'
	    )->addColumn(
	        'productlist_style',
	        Table::TYPE_TEXT,
            '2M',
	        ['nullable' => false],
	        'ProductList Style'
	    )->addColumn(
	        'productlist_url',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'ProductList Url'
	    )->addColumn(
	        'product_type',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'Ptoduct Type'
	    )->addColumn(
            'to_date',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Label To Date'
        )->addColumn(
	        'use_for_parent',
	        Table::TYPE_SMALLINT,
	        null,
	        ['nullable' => false, 'default' => '1'],
	        'Use for parent'
	    )->addColumn(
	        'is_active',
	        Table::TYPE_SMALLINT,
	        null,
	        ['nullable' => false, 'default' => '1'],
	        'Is Tab Active'
	    )->addColumn(
	        'conditions_serialized',
	        Table::TYPE_TEXT,
	        '2M',
	        [],
	        'Conditions Serialized'
	    )->addColumn(
	        'actions_serialized',
	        Table::TYPE_TEXT,
	        '2M',
	        [],
	        'Actions Serialized'
	    )->addColumn(
	        'stock_status',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'Stock Status'
	    )->addColumn(
	        'hide_lower_priority',
	        Table::TYPE_SMALLINT,
	        null,
	        ['nullable' => true],
	        'Hide labels with lower priority'
	    )->addColumn(
	        'product_stock_enabled',
	        Table::TYPE_SMALLINT,
	        null,
	        ['nullable' => false, 'default' => '1'],
	        'Use Stock Range'
	    )->addColumn(
	        'rule_stock_higher',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'Display if stock is higher than'
	    )->addColumn(
	        'rule_stock_lower',
	        Table::TYPE_TEXT,
	        255,
	        ['nullable' => false],
	        'Display if stock is lower than'
	    )->addIndex(
	        $setup->getIdxName(
	            $installer->getTable('mgz_productlabels_label'),
	            ['name'],
	            AdapterInterface::INDEX_TYPE_FULLTEXT
	        ),
	        ['name'],
	        ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
	    )->setComment(
	        'ProductLabels Label Table'
	    );
	    $installer->getConnection()->createTable($table);

	    /**
	     * Create table 'mgz_productlabels_label_store'
	     */
	    $table = $installer->getConnection()->newTable(
	        $installer->getTable('mgz_productlabels_label_store')
	    )->addColumn(
	        'label_id',
	        Table::TYPE_SMALLINT,
	        null,
	        ['nullable' => false, 'primary' => true],
	        'Tab Id'
	    )->addColumn(
	        'store_id',
	        Table::TYPE_SMALLINT,
	        null,
	        ['unsigned' => true, 'nullable' => false, 'primary' => true],
	        'Store ID'
	    )->addIndex(
	        $installer->getIdxName('mgz_productlabels_label_store', ['store_id']),
	        ['store_id']
	    )->addForeignKey(
	        $installer->getFkName('mgz_productlabels_label_store', 'label_id', 'mgz_productlabels_label', 'label_id'),
	        'label_id',
	        $installer->getTable('mgz_productlabels_label'),
	        'label_id',
	        Table::ACTION_CASCADE
	    )->addForeignKey(
	        $installer->getFkName('mgz_productlabels_label_store', 'store_id', 'store', 'store_id'),
	        'store_id',
	        $installer->getTable('store'),
	        'store_id',
	        Table::ACTION_CASCADE
	    )->setComment(
	        'Tab Store'
	    );
	    $installer->getConnection()->createTable($table);

		/**
	     * Create table 'mgz_productlabels_label_customergroup'
	     */
	    $table = $installer->getConnection()->newTable(
	        $installer->getTable('mgz_productlabels_label_customergroup')
	    )->addColumn(
	        'label_id',
	        Table::TYPE_SMALLINT,
	        null,
	        ['nullable' => false, 'primary' => true],
	        'Label Id'
	    )->addColumn(
	        'customer_group_id',
	        Table::TYPE_INTEGER,
	        null,
	        ['unsigned' => true, 'nullable' => false, 'primary' => true],
	        'Customer Group ID'
	    )->addIndex(
	        $installer->getIdxName('mgz_productlabels_label_customergroup', ['customer_group_id']),
	        ['customer_group_id']
	    )->addForeignKey(
	        $installer->getFkName('mgz_productlabels_label_customergroup', 'label_id', 'mgz_productlabels_label', 'label_id'),
	        'label_id',
	        $installer->getTable('mgz_productlabels_label'),
	        'label_id',
	        Table::ACTION_CASCADE
	    )->addForeignKey(
	        $installer->getFkName('mgz_productlabels_label_customergroup', 'customer_group_id', 'customer_group', 'customer_group_id'),
	        'customer_group_id',
	        $installer->getTable('customer_group'),
	        'customer_group_id',
	        Table::ACTION_CASCADE
	    )->setComment(
	        'Label Custom Group'
	    );
	    $installer->getConnection()->createTable($table);


	    /**
         * Create table 'mgz_productlabels_label_product'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgz_productlabels_label_product')
        )->addColumn(
            'label_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Tab ID'
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
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Product Id'
        )->addIndex(
            $installer->getIdxName('mgz_productlabels_label_product', ['product_id']),
            ['product_id']
        )->addIndex(
            $installer->getIdxName('mgz_productlabels_label_product', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('mgz_productlabels_label_product', 'label_id', 'mgz_productlabels_label', 'label_id'),
            'label_id',
            $installer->getTable('mgz_productlabels_label'),
            'label_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('mgz_productlabels_label_product', 'product_id', 'catalog_product_entity', 'entity_id'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('mgz_productlabels_label_product', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Label Product'
        );
        $installer->getConnection()->createTable($table);
	}
}