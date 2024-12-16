<?php
/*
 * @Date: 2024-12-13 16:11:55
 * @LastEditors: arvin
 * @LastEditTime: 2024-12-13 16:42:25
 * @FilePath: /app/code/Mitac/Theme/Setup/InstallData.php
 * @Description: 
 *   module setup 時 執行， 如修改， 確認刪除db module_setup中的該 module， 在執行 setup:upgrade 指令
 */
namespace Mitac\Theme\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Category;
class InstallData implements InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            Category::ENTITY,
            'theme_color',
            [
                'type' => 'varchar',
                'label' => 'Theme Color',
                'input' => 'select',
                'source' => \Mitac\Theme\Model\Attribute\Source\ThemeColor::class,
                'required' => false,
                'default' => 'orange',
                'visible_on_front' => true,
                'sort_order' => 300,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Design',
            ]
        );
        $setup->endSetup();
    }
}