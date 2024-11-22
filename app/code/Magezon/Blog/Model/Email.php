<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at http://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2021 Magezon (http://magezon.com)
 */

namespace Magezon\Blog\Model;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magezon\Blog\Helper\Data;

class Email extends AbstractHelper
{
    /**
     * @var ConfigInterface
     */
    protected $contactsConfig;

    /**
     * @var Escaper
     */
    protected $transportBuilder;

    /**
     * @var TransportBuilder
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        Data $dataHelper
    ) {
        parent::__construct($context);
        $this->contactsConfig = $contactsConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Send Email Template action
     *
     * @param $email
     * @param $name
     * @param $customerEmail
     * @param $commentDetail
     * @param $postLink
     * @param $param
     * @return $this
     */
    public function sendEmail($email, $name, $customerEmail, $commentDetail, $postLink, $param) {
        switch ($param) {
            case 1:
                $templateId = $this->dataHelper->getEmailTemplateAdmin();
                break;
            
            default:
                $templateId = $this->dataHelper->getEmailTemplateCustomer();
                break;
        }

        $storeId = $this->getStore()->getId();
        $this->sendEmailByTemplate(
            $email, 
            $templateId,
            [
                'name'              => $name,
                'customer_email'    => $customerEmail,
                'comment_detail'    => $commentDetail,
                'post_link'         => $postLink,
                'store'             => $this->getStore()
            ],
            $storeId
        );
        return $this;
    }

    /**
     * Send Email Template action
     *
     * @param $email
     * @param $templateId
     * @param $param
     * @param $storeId
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendEmailByTemplate ($email, $templateId, $param = [], $storeId = null)
    {
        $emailRecipient = $this->contactsConfig->emailRecipient();
        $emailAdmin = $this->collectionFactory->create()->getFirstItem()->getEmail();    
        $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $storeId,
        ];
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($param)
            ->setFrom([
                'email' => $emailAdmin,
                'name' => $this->getStore()->getName()
            ])
            ->addTo($email)
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     * get store name
     *
     * @return mixed
     */
    public function getStore()
    {
        $store = $this->storeManager->getStore();
        return $store;
    }
}