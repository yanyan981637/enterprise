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

namespace Magezon\ProductPageBuilder\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;

class AddPPDProfileIdProductAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeRepositoryInterface $attributeRepository,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $entityType = \Magento\Catalog\Model\Product::ENTITY;
        $attributeCode = 'ppd_profile_id';
        // $attribute = $this->attributeRepository->get($entityType, $attributeCode);

        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);

        if (!$attribute || !$attribute->getAttributeId()) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

            /**
             * Install eav entity types to the eav/entity_type table
             */
            $eavSetup->addAttribute(
                $entityType,
                $attributeCode,
                [
                    'group' => 'General',
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'type' => 'int',
                    'input' => 'select',
                    'label' => 'Product Page Builder Profile',
                    'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                    'frontend' => '',
                    'source' => \Magezon\ProductPageBuilder\Model\Source\ListProfile::class,
                    'visible' => 1,
                    'user_defined' => 1,
                    'used_for_price_rules' => 1,
                    'position' => 0,
                    'unique' => 0,
                    'sort_order' => 100,
                    'required' => 0,
                    'is_configurable' => 1,
                    'is_searchable' => 0,
                    'is_visible_in_advanced_search' => 0,
                    'is_comparable' => 0,
                    'is_filterable' => 0,
                    'is_filterable_in_search' => 1,
                    'is_used_for_promo_rules' => 1,
                    'is_html_allowed_on_front' => 0,
                    'is_visible_on_front' => 1,
                    'used_in_product_listing' => 0,
                    'used_for_sort_by' => 0,
                    'is_used_in_grid' => 1,
                    'is_filterable_in_grid' => 1
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
