<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Controller\Adminhtml\Translation;

class Regenerate extends \Magento\Backend\App\Action
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
     * Regenerate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magefan\TranslationPlus\Model\TranslationIndex $TranslationIndex
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magefan\TranslationPlus\Model\TranslationIndex $TranslationIndex
    ) {
        $this->translationIndex = $TranslationIndex;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Db_Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->getParam('use_cron')) {
            $this->translationIndex->updateSchema();
            $this->translationIndex->updateData();
        } else {
            $this->translationIndex->scheduleUpdate();
            $this->messageManager->addSuccessMessage(__('Data refresh has been scheduled and will be performed shortly by cron.'));
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
