<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Controller\Adminhtml\Action;

use Magefan\TranslationPlus\Model\TranslationIndex;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Inline Edit Grid Translation
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_Translation::search';

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magefan\TranslationPlus\Model\TranslationIndex
     */
    private $model;

    /**
     * @var \Magefan\Translation\Model\ResourceModel\Translation\CollectionFactory
     */
    private $translationCollectionFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context                                                                $context
     * @param JsonFactory                                                            $jsonFactory
     * @param TranslationIndex                                                       $model
     * @param \Magefan\Translation\Model\ResourceModel\Translation\CollectionFactory $translationCollectionFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        TranslationIndex $model,
        \Magefan\Translation\Model\ResourceModel\Translation\CollectionFactory $translationCollectionFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->model = $model;
        $this->translationCollectionFactory = $translationCollectionFactory;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelid) {
                    try {
                        $modelData = $this->model->load($modelid);
                        $modelData->addData($postItems[$modelid]);
                        $modelData->save();

                        $string = $modelData->getData('string');

                        foreach ($modelData->getAllStoreLocale() as $locale) {
                            $translate = $modelData->getData(strtolower($locale));
                            if (!$translate) {
                                continue;
                            }
                            if ($translate == $string) {
                                continue;
                            }

                            $translation = $this->translationCollectionFactory->create()
                                ->addFieldToFilter('crc_string', $modelData->getData('crc_string'))
                                ->addFieldToFilter('locale', $locale)
                                ->addFieldToFilter('store_id', 0)
                                ->setPageSize(1)
                                ->getFirstItem();
                            if ($translation->getTranslate() != $translate) {
                                $translation->addData(
                                    [
                                    'string' => $string,
                                    'translate' => $translate,
                                    'locale' => $locale,
                                    'crc_string' => $modelData->getData('crc_string'),
                                    'store_id' => 0
                                    ]
                                )->save();
                            }
                        }
                    } catch (\Exception $e) {
                        $messages[] = "[Error, item id: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData(
            [
            'messages' => $messages,
            'error' => $error
            ]
        );
    }
}
