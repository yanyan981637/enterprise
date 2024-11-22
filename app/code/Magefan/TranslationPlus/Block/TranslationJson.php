<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block;

use Magefan\Translation\Model\ResourceModel\Translation\CollectionFactory;
use Magefan\Translation\Model\Config;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\DataObject\IdentityInterface;
use Magefan\TranslationPlus\Model\Config as ModelConfig;

/**
 * Class Check EnableInfo Block
 */
class TranslationJson extends \Magento\Framework\View\Element\Text implements IdentityInterface
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
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var File
     */
    private $driverFile;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var mixed
     */
    private $jsTranslationFile;

    /**
     * @var \Magefan\Translation\Model\ResourceModel\Translation\Collection
     */
    private $translationsCollection;

    /**
     * @var ModelConfig
     */
    private $modelConfig;

    /**
     * TranslationJson constructor.
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param Resolver $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param Source $source
     * @param File $driverFile
     * @param Json $jsonSerializer
     * @param ModelConfig $modelConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $config,
        Resolver $localeResolver,
        StoreManagerInterface $storeManager,
        Source $source,
        File $driverFile,
        Json $jsonSerializer,
        ModelConfig $modelConfig,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->source = $source;
        $this->driverFile = $driverFile;
        $this->jsonSerializer = $jsonSerializer;
        $this->assetRepository = $context->getAssetRepository();
        $this->modelConfig = $modelConfig;
        parent::__construct($context, $data);
    }


    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getJsTranslationFile()
    {
        if (null === $this->jsTranslationFile) {
            $asset = $this->assetRepository->createAsset('js-translation.json');
            $file = $this->source->getFile($asset);

            $file = str_replace('var/view_preprocessed/', '', $file);

            if ($this->driverFile->isExists($file)) {
                $this->jsTranslationFile = $file;
            } else {
                $this->jsTranslationFile = false;
            }
        }
        return $this->jsTranslationFile;
    }

    /**
     * @param $locale
     * @param $lastUpdateDate
     * @return \Magefan\Translation\Model\ResourceModel\Translation\Collection
     */
    private function getTranslationsCollection()
    {
        if (null === $this->translationsCollection) {
            $file = $this->getJsTranslationFile();
            $locale = $this->localeResolver->getLocale();
            $storeId = $this->storeManager->getStore()->getId();

            $staticContentDeployDateTime = $this->modelConfig->getStaticContentDeployDateTime();
            if ($staticContentDeployDateTime) {
                $date = date('Y-m-d H:i:s', strtotime($staticContentDeployDateTime)  - 86400);
            } else {
                $date = date('Y-m-d H:i:s', filemtime($file) - 86400);
            }

            $translations = $this->collectionFactory->create();
            $translations
                ->addFieldToFilter('locale', $locale)
                ->addFieldToFilter(
                    'updated_at',
                    ['gt' => $date]
                )
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
     * @return string
     */
    private function getTranslationJson(): string
    {
        $translations = $this->getTranslationsCollection();
        $data = [];
        foreach ($translations as $translate) {
            $data[$translate->getData('string')] = $translate->getData('translate');
        }

        if ($data) {
            return $this->jsonSerializer->serialize($data);
        }

        return '';
    }

    /**
     * Override this method in descendants to produce html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canDisplay()) {
            return '';
        }

        $json = $this->getTranslationJson();
        if ($json) {
            return '<script>' .
                'window.mfTranslationJson = ' . $json . ';' .
                '</script>';
        }

        return '';
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function canDisplay()
    {
        if (!$this->config->isEnabled()
            || !$this->getJsTranslationFile()
            || $this->driverFile->isWritable($this->getJsTranslationFile())
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve identities
     *
     * @return array
     */
    public function getIdentities()
    {
        if ($this->canDisplay()) {
            $identities = [];
            $identities[] = \Magefan\Translation\Model\Translation::CACHE_TAG . '_' . 0;
            /*
            foreach ($this->getTranslationsCollection() as $item) {
                $identities = array_merge($identities, $item->getIdentities());
            }
            */

            return array_unique($identities);
        }

        return [];
    }
}
