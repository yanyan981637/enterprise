<?php
/**
 *
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 *
 */
namespace PluginCompany\ContactForms\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $setup;

    /**
     * Updates DB schema for Contact Forms extension
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->setup = $setup;
        $setup->startSetup();
        $this->increaseFormHtmlAndJSONFieldsLength();
        $this->addTranslationField();
        $this->addEmailTemplateFields();
        $setup->endSetup();
    }

    private function increaseFormHtmlAndJSONFieldsLength()
    {
        $this->setup->getConnection()
            ->modifyColumn(
                $this->setup->getTable('plugincompany_contactforms_form'),
                'contact_form_json',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Contact Form JSON',
                    'length' => '2m'
                ]
            )
            ->modifyColumn(
                $this->setup->getTable('plugincompany_contactforms_form'),
                'contact_form_html',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Contact Form HTML',
                    'length' => '2m'
                ]
            )
        ;
    }

    private function addTranslationField()
    {
        $this->setup->getConnection()
            ->addColumn(
                $this->setup->getTable('plugincompany_contactforms_form'),
                'translation',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Fields Translation',
                    'length' => '2m'
                ]
            );
        $this->setup->getConnection()
            ->addColumn(
                $this->setup->getTable('plugincompany_contactforms_form'),
                'general_translation',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'General Translation',
                    'length' => '2m'
                ]
            );
    }

    private function addEmailTemplateFields()
    {
        $this->setup->getConnection()
            ->addColumn(
                $this->setup->getTable('plugincompany_contactforms_form'),
                'customer_notification_template',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Customer Notification Template',
                    'length' => 255,
                    'default' => 'custom_contact_forms_notification'
                ]
            );
        $this->setup->getConnection()
            ->addColumn(
                $this->setup->getTable('plugincompany_contactforms_form'),
                'admin_notification_template',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Admin Notification Template',
                    'length' => 255,
                    'default' => 'custom_contact_forms_notification'
                ]
            );
    }
}


