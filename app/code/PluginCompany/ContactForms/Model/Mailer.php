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
namespace PluginCompany\ContactForms\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Transport;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use PluginCompany\ContactForms\Model\Mailer\AttachmentManager;

class Mailer
{
    private $mailData;
    /** @var  Entry */
    private $entry;
    /** @var TransportBuilder */
    private $transportBuilder;
    /** @var Transport */
    private $transport;
    /** @var ScopeConfigInterface */
    private $scopeConfig;
    /**
     * @var AttachmentManager
     */
    private $attachmentManager;

    /**
     * Mailer constructor.
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param AttachmentManager $attachmentManager
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        AttachmentManager $attachmentManager
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->attachmentManager = $attachmentManager;
    }

    public function sendCustomerNotification()
    {
        if(!$this->getEntry()){
            $this->throwNoEntryLoadedException();
        }

        $this->initMailData();

        $entry = $this->getEntry();

        $this->mailData
            ->setToEmail(
                $entry->getCustomerEmail()
            )->setToName(
                $entry->getCustomerName()
            )->setBcc(
                ($entry->getCustomerBcc() ? explode(',', $entry->getCustomerBcc()) : null)
            )->setBody(
                $entry->getCustomerNotification()
            )->setSubject(
                $entry->getCustomerSubject()
            )->setFromName(
                $entry->getSenderName()
            )->setFromEmail(
                $entry->getSenderEmail()
            )->setTemplateIdentifier(
                $entry->getForm()->getCustomerNotificationTemplate()
            )
            ->setReplyTo(null)
        ;
        $this->sendMail();

        return $this;
    }

    private function throwNoEntryLoadedException()
    {
        throw new \Exception("No entry loaded, unable to send e-mail");
    }

    private function initMailData()
    {
        $this->mailData = new DataObject();
        return $this;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry($entry)
    {
        $this->entry = $entry;
        return $this;
    }

    public function sendAdminNotification()
    {
        if(!$this->getEntry()){
            $this->throwNoEntryLoadedException();
        }

        $this->initMailData();

        $entry = $this->getEntry();

        $this->mailData
            ->setToEmail(
                $entry->getAdminEmail()
            )->setToName(
                null
            )->setBcc(
                ($entry->getAdminBcc() ? explode(',', $entry->getAdminBcc()) : null)
            )->setBody(
                $entry->getAdminNotification()
            )->setSubject(
                $entry->getAdminSubject()
            )->setFromName(
                $entry->getAdminSenderName()
            )->setFromEmail(
                $entry->getAdminSenderEmail()
            )->setReplyTo(
                $entry->getAdminReplyToEmail()
            )->setTemplateIdentifier(
                $entry->getForm()->getAdminNotificationTemplate()
            )->setAttachments(
                $this->getAttachments()
            )
        ;
        $this->sendMail();

        return $this;
    }

    public function getAttachments()
    {
        if(!$this->attachmentsAllowed()) {
            return [];
        }
        return $this->getEntry()->getAllUploadedFilePaths();
    }

    public function attachmentsAllowed()
    {
        return $this->scopeConfig->isSetFlag('plugincompany_contactforms/admin_notification/include_uploads', 'store');
    }

    public function sendMail()
    {
        $mailData = $this->mailData;
        $transportBuilder = $this->transportBuilder
            ->setTemplateIdentifier($mailData->getTemplateIdentifier())
            ->setTemplateOptions(
                [
                    'area' => 'frontend',
                    'store' => $this->getEntry()->getStoreId()
                ]
            )
            ->setTemplateVars(
                [
                    'content' => $mailData->getBody(),
                    'content_plain' => strip_tags($mailData->getBody()),
                    'email_subject' => $mailData->getSubject()
                ]
            )
            ->setFrom(
                [
                    'email' => $mailData->getFromEmail(),
                    'name' => $mailData->getFromName()
                ]
            )
            ->addTo($this->getToEmail(), $mailData->getToName())
        ;
        if($mailData->getReplyTo()) {
            $transportBuilder->setReplyTo($mailData->getReplyTo());
        }
        if($this->getBcc()){
            $transportBuilder->addBcc($this->getBcc());
        }

        $this->attachmentManager->setAttachmentsToAdd($mailData->getAttachments());
        $this->transport = $transportBuilder->getTransport();
        $this->attachmentManager->setAttachmentsToAdd([]);

        $this->transport->sendMessage();
        return $this;
    }

    public function getToEmail()
    {
        return $this->explodeIfCommaDelimited(
            $this->mailData->getData('to_email') ?? ''
        );
    }

    public function getBcc()
    {
        return $this->explodeIfCommaDelimited(
            $this->mailData->getData('bcc') ?? ''
        );
    }

    private function explodeIfCommaDelimited($mail)
    {
        if(is_string($mail) && strstr($mail,',')){
            $mail = explode(',', $mail);
        }
        if(is_array($mail)) {
            $mail = array_map('trim', $mail);
        }
        return $mail;
    }

}
