<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model;

use Magento\Setup\Module\I18n\ServiceLocator;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\FileSystemException;

class PhrasesTranslations
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var File
     */
    private $file;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Emulation $emulation
     * @param Manager $cacheManager
     * @param DirectoryList $directoryList
     * @param Csv $csv
     * @param File $file
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Emulation $emulation,
        Manager $cacheManager,
        DirectoryList $directoryList,
        Csv $csv,
        File $file
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->emulation = $emulation;
        $this->cacheManager = $cacheManager;
        $this->directoryList = $directoryList;
        $this->csv = $csv;
        $this->file = $file;
    }

    /**
     * @return array
     * @throws FileSystemException
     */
    public function getData(): array
    {
        $phrasesFilePath = $this->directoryList->getPath('var') . '/mf_translations.csv';

        try {
            ServiceLocator::getDictionaryGenerator()->generate($this->directoryList->getRoot(), $phrasesFilePath, true);
        } catch (\Exception $e) {

        } finally {
            $keys = $this->getTranslationsFromCsvFile($phrasesFilePath);

            $storeCodes = [];
            $result = [];
            $this->cacheManager->clean(['translate']);

            foreach ($this->storeManager->getStores() as $store) {
                if (!$store->getIsActive()) {
                    continue;
                }

                $localeCode = $this->scopeConfig->getValue(
                    'general/locale/code',
                    ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );
                $localeCode = strtolower($localeCode);

                if (array_key_exists($localeCode, $storeCodes)) {
                    continue;
                }

                $storeCodes[$localeCode] = true;

                $this->emulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);
                foreach ($keys as $key) {
                    if (!empty($key)) {
                        if (!isset($result[$key])) {
                            $result[$key] = [
                                'string' => $key,
                                'crc_string' => crc32($key),
                                'source' => 'phrases collector'
                            ];
                        }

                        $result[$key][$localeCode] = (string)__($key);
                        $result[$key][$localeCode . '_translated'] = ($result[$key][$localeCode] == $key) ? 0 : 1;
                    }
                }

                $this->emulation->stopEnvironmentEmulation();
            }

            return $result;
        }
    }

    /**
     * @param string $csvFile
     * @return array
     * @throws \Exception
     */
    private function getTranslationsFromCsvFile(string $csvFile):array
    {
        $result = [];

        try {
            if ($this->file->isExists($csvFile)) {
                $this->csv->setDelimiter(",");
                $data = $this->csv->getData($csvFile);
                if (!empty($data)) {
                    foreach (array_slice($data, 1) as $key => $value) {
                        $result[$value[0]] = $value[0];
                    }
                }

                $this->file->deleteFile($csvFile);
            }
        } catch (FileSystemException $e) {

        }

        return $result;
    }
}
