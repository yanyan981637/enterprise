<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Controller\Adminhtml\Translation;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Translation Index Grid Page
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_Translation::search';

    /**
     * @var \Magefan\TranslationPlus\Model\TranslationIndex
     */
    private $translationIndex;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magefan\Translation\Api\ConfigInterface
     */
    private $configInterface;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magefan\TranslationPlus\Model\TranslationIndex $TranslationIndex
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magefan\Translation\Api\ConfigInterface $configInterface
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magefan\TranslationPlus\Model\TranslationIndex $TranslationIndex,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magefan\Translation\Api\ConfigInterface $configInterface,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->translationIndex = $TranslationIndex;
        $this->configInterface = $configInterface;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Db_Exception
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        if (!$this->configInterface->isEnabled()) {
            $this->messageManager->addError(
                __(
                    strrev(
                        'noitalsnarT> snoisnetxE nafegaM > noitarugifnoC >
            serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si noitalsnarT nafegaM'
                    )
                )
            );
            $redirect = $this->resultRedirectFactory->create();
            return $redirect->setPath('admin/index/index');
        }

        if (!$this->translationIndex->isSchemaExist()) {
            $this->translationIndex->installSchema();
        }

        $refreshStatsLink = $this->_url->getUrl('*/*/regenerate');
        $refreshStatsLinkByCron = $this->_url->getUrl('*/*/regenerate', ['use_cron' => true]);
        $lastUpdate = $this->translationIndex->getUpdateDataAt() ?: __('Never');

        $this->messageManager->addNotice(
            __(
                'Last updated: %1. To refresh data, ' .
                '<a href="%2" >click here</a>.
                If the process goes too long or stops with an error 
                <a href="%3" >click here</a> to run it asynchronously by cron.',
                $lastUpdate,
                $refreshStatsLink,
                $refreshStatsLinkByCron
            )
        );
        $resultPage->getConfig()->getTitle()->prepend(__("Search and Translate"));

        return $resultPage;
    }
}
