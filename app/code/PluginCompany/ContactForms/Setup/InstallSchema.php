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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $_setup;

    /**
     * Installs DB schema for Contact Forms extension
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $this->_setup = $setup;

        $setup->startSetup();

        $this
            ->createMainFormTable()
            ->createFormStoreRelationTable()
            ->createFormEntryTable()
            ->createEntryCommentTable();

        //end create table

        $setup->endSetup();
    }

    /**
     * Create Contact Forms main table
     * @return $this
     */
    private function createMainFormTable()
    {
        $installer = $this->_setup;

        $table = $installer->getConnection()->newTable(
            $installer->getTable('plugincompany_contactforms_form')
        )
            ->addColumn('entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'identity' => true, 'nullable' => false, 'primary' => true, ],
                'Form ID')
            ->addColumn('title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Form Title')
            ->addColumn('enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, ],
                'Enable Form')
            ->addColumn('frontend_page',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, ],
                'Create Front-end URL')
            ->addColumn('enable_captcha',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, ],
                'Enable Captcha')
            ->addColumn('notify_customer',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, ],
                'Enable Customer Notification')
            ->addColumn('customer_from_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'From Name')
            ->addColumn('customer_from_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'From e-mail address')
            ->addColumn('customer_mail_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Notification Subject')
            ->addColumn('customer_mail_bcc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Send e-mail copy to')
            ->addColumn('customer_mail_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [ 'nullable' => false, ],
                'Customer notification content')
            ->addColumn('contact_form_html',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Contact Form HTML')
            ->addColumn('contact_form_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Contact Form JSON')
            ->addColumn('notify_admin',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, ],
                'Enable Admin Notification')
            ->addColumn('admin_from_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'From Name')
            ->addColumn('admin_from_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'From e-mail address')
            ->addColumn('admin_to_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'To e-mail address')
            ->addColumn('admin_mail_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Notification Subject')
            ->addColumn('admin_mail_bcc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Send e-mail copy to')
            ->addColumn('admin_notification_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [ 'nullable' => false, ],
                'Notification Content')
            ->addColumn('form_wrapper',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'The front-end form wrapper')
            ->addColumn('frontend_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Frontend form title')
            ->addColumn('theme',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Front-end theme')
            ->addColumn('max_width',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Form max-width')
            ->addColumn('show_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [],
                'Show Title on front end')
            ->addColumn('url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'URL key')
            ->addColumn('status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Form Status')
            ->addColumn('updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Form Modification Time')
            ->addColumn('created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Form Creation Time')
            ->addColumn('frontend_success_message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Frontend from submit message')
            ->addColumn('in_window',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Lightbox yes no')
            ->addColumn('window_link_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link text')
            ->addColumn('rtl',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => 0
                ],
                'Use RTL yesno')
            ->addColumn('enable_entries',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'nullable' => false, 'default' => 1 ],
                'Enable saving form entries yes/no')
            ->addColumn('customer_to_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => false ],
                'Customer notification recipient address')
            ->addColumn('customer_to_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => false, ],
                'Customer notification recipient name')
            ->addColumn('dependent_fields',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Dependent Fields JSON')
            ->addColumn('arbitrary_js',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Arbitrary JS on Form submission')
            ->addColumn('condit_to_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Conditional Notification Recipient in JSON array')
            ->addColumn('admin_reply_to_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Reply to e-mail for admin notification')
            ->addColumn('css_classes',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Form custom CSS classes')
            ->addColumn('custom_css',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Form custom CSS')
            ->addColumn('pageload_js',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, 'after' => 'arbitrary_js' ],
                'Pageload custom JS')
            ->addColumn('beforesubmit_js',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, 'after' => 'arbitrary_js' ],
                'Before form submit custom JS')
            ->addColumn('label_style',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['default' => 1],
                'Label Style')
            ->addColumn('entry_increment_prefix',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Form entry increment ID prefix')
            ->addColumn('entry_increment_id_counter',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, 'default' => 0, ],
                'Last increment counter')
            ->setComment('Form Table');
        $installer->getConnection()->createTable($table);
        return $this;
    }

    private function createFormStoreRelationTable()
    {

        $installer = $this->_setup;
        $table = $installer->getConnection()->newTable(
            $installer->getTable('plugincompany_contactforms_form_store')
        )
            ->addColumn('form_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'nullable' => false, 'primary' => true, ],
                'Form ID'
            )
            ->addColumn('store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true, 'nullable' => false, 'primary' => true, ],
                'Store ID'
            )
            ->addIndex(
                $installer->getIdxName(
                    'plugincompany_contactforms_form_store',
                    ['store_id']
                ),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'plugincompany_contactforms_form_store',
                    'form_id',
                    'plugincompany_contactforms_form',
                    'entity_id'
                ),
                'form_id',
                $installer->getTable('plugincompany_contactforms_form'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'plugincompany_contactforms_form_store',
                    'store_id',
                    'core_store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )

            ->setComment('Forms To Store Linkage Table');

        $installer->getConnection()->createTable($table);

        return $this;
    }

    private function createFormEntryTable()
    {
        $installer = $this->_setup;
        $table = $installer->getConnection()->newTable(
            $installer->getTable('plugincompany_contactforms_entry')
        )
            ->addColumn('entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'identity' => true, 'nullable' => false, 'primary' => true, ],
                'Form Entry ID')
            ->addColumn('form_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'unsigned' => true, ],
                'Form ID')
            ->addColumn('store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'nullable' => false, ],
                'Store')
            ->addColumn('customer_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Customer Name')
            ->addColumn('customer_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Customer e-mail')
            ->addColumn('customer_bcc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Customer BCC')
            ->addColumn('sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Sender Name')
            ->addColumn('sender_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Sender e-mail')
            ->addColumn('customer_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Customer Notification Subject')
            ->addColumn('customer_notification',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [ 'nullable' => false, ],
                'Customer Notification')
            ->addColumn('admin_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Admin e-mail')
            ->addColumn('admin_bcc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Admin BCC')
            ->addColumn('admin_notification',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [ 'nullable' => false, ],
                'Admin Notification')
            ->addColumn('admin_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Admin Notification Subject')
            ->addColumn('admin_sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => true, ],
                'Admin Sender Name')
            ->addColumn('admin_sender_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Admin Sender Email')
            ->addColumn('admin_reply_to_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => true, ],
                'Admin Reply-to Email')
            ->addColumn('customer_notification_sent',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => true, ],
                'Is Customer Notification Sent')
            ->addColumn('admin_notification_sent',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => true, ],
                'Is Admin Notification Sent')
            ->addColumn('fields',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [ 'nullable' => false, ],
                'Form Fields')
            ->addColumn('status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'default' => 0, 'nullable' => false ],
                'Form Entry Status')
            ->addColumn('updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Form Entry Modification Time')
            ->addColumn('created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Form Entry Creation Time')
            ->addColumn('increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => true, ],
                'Form entry increment ID')
            ->addColumn('increment_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true, ],
                'Form entry increment')
            ->addColumn('upload_dir',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => true, ],
                'Upload Dir')
            ->addIndex(
                $installer->getIdxName(
                    'plugincompany_contactforms_form',
                    ['form_id']
                ),
                ['form_id']
            )
            ->setComment('Form Entry Table');;
        $installer->getConnection()->createTable($table);
        return $this;
    }

    private function createEntryCommentTable()
    {
        $installer = $this->_setup;
        $table = $installer->getConnection()->newTable(
            $installer->getTable('plugincompany_contactforms_entry_comment')
        )
            ->addColumn('comment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'identity' => true, 'nullable' => false, 'primary' => true, ],
                'Comment ID')
            ->addColumn('form_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'nullable' => false, 'primary' => true, ],
                'Form ID')
            ->addColumn('entry_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'nullable' => false, 'primary' => true, ],
                'Form Entry ID')
            ->addColumn('admin_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, ],
                'Admin User ID')
            ->addColumn('created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Comment Creation Date')
            ->addColumn('content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Comment Contents')
            ->addForeignKey(
                $installer->getFkName(
                    'plugincompany_contactforms_entry_comment',
                    'entry_id',
                    'plugincompany_contactforms_entry',
                    'entity_id'
                ),
                'entry_id',
                $installer->getTable('plugincompany_contactforms_entry'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Form submissions comments table');
        $installer->getConnection()->createTable($table);
        return $this;
    }

}
