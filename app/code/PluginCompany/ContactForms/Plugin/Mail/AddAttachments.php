<?php
namespace PluginCompany\ContactForms\Plugin\Mail;

use PluginCompany\ContactForms\Model\Mailer;

class AddAttachments
{

    /**
     * @var Mailer\AttachmentManager
     */
    private $attachmentManager;

    public function __construct(
        Mailer\AttachmentManager $attachmentManager
    )
    {
        $this->attachmentManager = $attachmentManager;
    }

    public function beforeCreate( $subject, $data)
    {
        if(!$this->attachmentManager->hasAttachmentsToAdd()) {
            return [$data];
        }
        //Magento 2.2.7 and earlier
        if(isset($data['message']) && $data['message'] instanceof \Zend_Mail) {
            $this->attachmentManager->addAttachmentsOldZend($data['message']);
            return [$data];
        }
        //Magento 2.2.8 - 2.3.2
        if(isset($data['message']) && !($data['message'] instanceof \Magento\Framework\Mail\EmailMessage)) {
            $this->attachmentManager->addAttachmentsNewZend($data['message']);
            return [$data];
        }
        //Magento 2.3.3 unpatched / patched
        $data['parts'] = array_merge($data['parts'], $this->attachmentManager->getAttachmentsToAddAsMimeParts());
        $this->attachmentManager->setAttachmentsToAdd([]);
        return [$data];
    }


}

