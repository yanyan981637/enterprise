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

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
      private $eavSetupFactory;

      /**
       * @param BrandFactory $brandFactory 
       * @param GroupFactory $groupFactory 
       */
      public function __construct(
           EavSetupFactory $eavSetupFactory
      ) {
            $this->eavSetupFactory = $eavSetupFactory;
      }

      public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
      {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'ppd_profile_id')) {
                  $eavSetup->addAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        'ppd_profile_id',
                        [
                              'group'                         => 'General',
                              'global'                        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                              'type'                          => 'int',
                              'input'                         => 'select',
                              'label'                         => 'Product Page Builder Profile',
                              'backend'                       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                              'frontend'                      => '',
                              'source'                        => 'Magezon\ProductPageBuilder\Model\Source\ListProfile',
                              'visible'                       => 1,
                              'user_defined'                  => 1,
                              'used_for_price_rules'          => 1,
                              'position'                      => 0,
                              'unique'                        => 0,
                              'sort_order'                    => 100,
                              'required'                      => 0,
                              'is_configurable'               => 1,
                              'is_searchable'                 => 0,
                              'is_visible_in_advanced_search' => 0,
                              'is_comparable'                 => 0,
                              'is_filterable'                 => 0,
                              'is_filterable_in_search'       => 1,
                              'is_used_for_promo_rules'       => 1,
                              'is_html_allowed_on_front'      => 0,
                              'is_visible_on_front'           => 1,
                              'used_in_product_listing'       => 0,
                              'used_for_sort_by'              => 0,
                              'is_used_in_grid'               => 1,
                              'is_filterable_in_grid'         => 1
                        ]
                  );
            }
      }
}