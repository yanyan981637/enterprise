<?php
/**
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
 */
namespace PluginCompany\ContactForms\Model\Mailer;

class AttachmentManager
{

    private $attachmentsToAdd = [];

    public function getAttachmentsToAdd()
    {
        return $this->attachmentsToAdd;
    }

    public function hasAttachmentsToAdd()
    {
        return isset($this->attachmentsToAdd) && !empty($this->attachmentsToAdd);
    }

    public function setAttachmentsToAdd($value)
    {
        $this->attachmentsToAdd = $value;
        return $this;
    }

    /**
     * @param \Zend_Mail $message
     * @return $this
     */
    public function addAttachmentsOldZend($message)
    {
        $paths = $this->getAttachmentsToAdd();
        foreach($paths as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }
            $this->addAttachmentOldZend($message, $filePath);
        }
        return $this;
    }

    /**
     * @param \Zend_Mail $message
     * @param $filePath
     */
    private function addAttachmentOldZend($message, $filePath)
    {
        $message->createAttachment(
            file_get_contents($filePath),
            finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath),
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $this->getFileNameFromPath($filePath)
        );
    }

    /**
     * @param \Magento\Framework\Mail\Message $message
     * @return $this
     */
    public function addAttachmentsNewZend($message)
    {
        $paths = $this->getAttachmentsToAdd();
        foreach($paths as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }
            $this->addAttachmentNewZend($message, $filePath);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Mail\Message $message
     * @param $filePath
     */
    public function addAttachmentNewZend($message, $filePath)
    {
        $body = $message->getBody();
        $body->addPart(
            $this->createAttachmentNewZend($filePath)
        );
        $message->setBodyHtml($body);
    }

    public function getAttachmentsToAddAsMimeParts()
    {
        $parts = [];
        if(!$this->hasAttachmentsToAdd()) {
            return $parts;
        }
        foreach($this->getAttachmentsToAdd() as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }
            $parts[] = $this->createAttachmentNewZend($filePath);
        }
        return $parts;
    }

    public function createAttachmentNewZend($filePath)
    {
        $mp = new \Zend\Mime\Part(file_get_contents($filePath));
        $mp->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath);
        $mp->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
        $mp->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
        $mp->filename = $this->getFileNameFromPath($filePath);
        return $mp;
    }


    private function getFileNameFromPath($path)
    {
        $parts = explode('/', $path);
        return array_pop($parts);
    }



}
