<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magefan\TranslationPlus\Controller\Translations;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magefan\TranslationPlus\Model\GetTranslationJson;

class Get extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultPageFactory;

    /**
     * @var GetTranslationJson
     */
    private $getTranslationJson;

    /**
     * @param Context $context
     * @param JsonFactory $resultPageFactory
     * @param GetTranslationJson $getTranslationJson
     */
    public function __construct(
        Context $context,
        JsonFactory $resultPageFactory,
        GetTranslationJson $getTranslationJson
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->getTranslationJson = $getTranslationJson;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->getResponse()->setPublicHeaders(300);
        $resultJson = $this->resultPageFactory->create();
        return $resultJson->setData($this->getTranslationJson->execute());
    }
}
