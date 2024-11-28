requirejs.config({
    map: {
        '*': {
            'css': 'PluginCompany_ContactForms/lib/require-css/css'
        }
    }
});
define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'underscore',
    'ko',
    'css!PluginCompany_ContactForms/css/htmlBox'
],function(
    Abstract,
    $,
    _,
    ko
) {
    var translatableFields = {
        frontend_title: {
            inputType: 'input',
            label: 'Form title',
            group: 'Front-end Style'
        },
        frontend_success_message: {
            inputType: 'textarea',
            label: 'Succcess message',
            group: 'Front-end Style'
        },
        customer_to_name: {
            inputType: 'input',
            label: 'Recipient name',
            group: 'Customer Notification'
        },
        customer_to_email: {
            inputType: 'input',
            label: 'Recipient e-mail address',
            group: 'Customer Notification'
        },
        customer_mail_bcc: {
            inputType: 'input',
            label: 'BCC recipients',
            group: 'Customer Notification'
        },
        customer_from_name: {
            inputType: 'input',
            label: 'Sender name',
            group: 'Customer Notification'
        },
        customer_from_email: {
            inputType: 'input',
            label: 'Sender e-mail address',
            group: 'Customer Notification'
        },
        customer_mail_subject: {
            inputType: 'input',
            label: 'Notification subject',
            group: 'Customer Notification'
        },
        customer_mail_content: {
            inputType: 'textarea',
            label: 'Notification content',
            group: 'Customer Notification'
        },
        admin_to_email: {
            inputType: 'input',
            label: 'Notification recipient',
            group: 'Admin Notification'
        },
        admin_mail_bcc: {
            inputType: 'input',
            label: 'BCC recipient(s)',
            group: 'Admin Notification'
        },
        admin_from_name: {
            inputType: 'input',
            label: 'Sender name',
            group: 'Admin Notification'
        },
        admin_from_email: {
            inputType: 'input',
            label: 'Sender e-mail address',
            group: 'Admin Notification'
        },
        admin_reply_to_email: {
            inputType: 'input',
            label: 'Reply to e-mail address',
            group: 'Admin Notification'
        },
        admin_mail_subject: {
            inputType: 'input',
            label: 'Notification subject',
            group: 'Admin Notification'
        },
        admin_notification_content : {
            inputType: 'textarea',
            label: 'Notification content',
            group: 'Admin Notification'
        }
    }
    var imports  = {};
    _.each(translatableFields, function(value, id) {
        imports["fields." + id + '.original'] = '${ $.provider }:data.' + id;
    })
    return Abstract.extend({
        defaults: {
            dataScope: 'general_translation',
            provider: 'plugincompany_form_form.form_form_data_source',
            fields: translatableFields,
            imports: imports
        },
        initialize: function () {
            this._super();
        },
    });
});
