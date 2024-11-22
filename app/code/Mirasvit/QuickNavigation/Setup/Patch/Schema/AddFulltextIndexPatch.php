<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\QuickNavigation\Setup\Patch\Schema;


use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Mirasvit\QuickNavigation\Api\Data\SequenceInterface;


class AddFulltextIndexPatch implements DataPatchInterface, PatchVersionInterface
{
    private $setup;
    
    public function __construct(ModuleDataSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public static function getVersion()
    {
        return '1.0.2';
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $setup = $this->setup;
        
        $setup->getConnection()->startSetup();
        
        $setup->getConnection()->dropIndex(
            $setup->getTable(SequenceInterface::TABLE_NAME),
            $setup->getConnection()->getIndexName(
                $setup->getTable(SequenceInterface::TABLE_NAME),
                SequenceInterface::SEQUENCE
            )
        );

        $setup->getConnection()->addIndex(
            $setup->getTable(SequenceInterface::TABLE_NAME),
            $setup->getConnection()->getIndexName(
                $setup->getTable(SequenceInterface::TABLE_NAME),
                SequenceInterface::SEQUENCE
            ),
            [SequenceInterface::SEQUENCE],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );

        $setup->getConnection()->endSetup();
    }
}