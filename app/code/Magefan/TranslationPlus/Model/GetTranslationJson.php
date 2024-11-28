<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model;

use Magefan\Translation\Model\ResourceModel\Translation\CollectionFactory;
use Magefan\Translation\Model\Config;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;

/**
 * Class Check EnableInfo Block
 */
class GetTranslationJson
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var File
     */
    private $driverFile;

    /**
     * @var mixed
     */
    private $jsTranslationFile;

    /**
     * @var \Magefan\Translation\Model\ResourceModel\Translation\Collection
     */
    private $translationsCollection;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param Resolver $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param File $driverFile
     * @param AssetRepository $assetRepository
     * @param DirectoryList $directoryList
     * @param RequestInterface|null $request
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Config $config,
        Resolver $localeResolver,
        StoreManagerInterface $storeManager,
        File $driverFile,
        AssetRepository $assetRepository,
        DirectoryList $directoryList,
        RequestInterface $request = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->driverFile = $driverFile;
        $this->assetRepository = $assetRepository;
        $this->directoryList = $directoryList;
        $this->request = $request ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(RequestInterface::class);
    }


    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getJsTranslationFile()
    {
        if (null === $this->jsTranslationFile) {
            $asset = $this->assetRepository->createAsset('js-translation.json');

            $file = $this->directoryList->getPath('static')  . '/' . $asset->getPath();

            if ($this->driverFile->isExists($file)) {
                $this->jsTranslationFile = $file;
            } else {
                $this->jsTranslationFile = false;
            }
        }
        return $this->jsTranslationFile;
    }

    /**
     * @return \Magefan\Translation\Model\ResourceModel\Translation\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTranslationsCollection()
    {
        if (null === $this->translationsCollection) {
            $file = $this->getJsTranslationFile();

            $isDisableTranslationParams = (bool)$this->config->getConfig('mftranslation/general/disable_translation_config_params');
            if ($isDisableTranslationParams) {
                $locale = null;
                $storeId = null;
            } else {
                $locale = (string)$this->request->getParam('locale');
                $storeId = (int)$this->request->getParam('store_id');
            }

            if (!$locale) {
                $locale = $this->localeResolver->getLocale();
            }
            if (!$storeId) {
                $storeId = $this->storeManager->getStore()->getId();
            }

            $translations = $this->collectionFactory->create();
            $translations
                ->addFieldToFilter('locale', $locale)
                ->addFieldToFilter(
                    ['store_id', 'store_id'],
                    [
                        ['eq' => 0],
                        ['eq' => $storeId],
                    ]
                );

            $this->translationsCollection = $translations;
        }

        return $this->translationsCollection;
    }

    /**
     * @return array
     */
    private function getTranslationJson(): array
    {
        $translations = $this->getTranslationsCollection();
        $data = [];
        foreach ($translations as $translate) {
            $data[$translate->getData('string')] = $translate->getData('translate');
        }

        return $data;
    }

    /**
     * Override this method in descendants to produce html
     *
     * @return array
     */
    public function execute(): array
    {
        if (!$this->config->isEnabled()
            || !$this->getJsTranslationFile()
            || $this->driverFile->isWritable($this->getJsTranslationFile())
        ) {
            return [];
        }

        return $this->getTranslationJson();
    }

}
