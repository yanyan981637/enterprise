<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model;

use Magefan\TranslationPlus\Model\I18n\ServiceLocator;
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
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class PhrasesTranslations
{

    CONST MODULE_PATH_SEPARATOR = '__MF__';

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
     * @var MessageManagerInterface
     */
    private $massageManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Emulation $emulation
     * @param Manager $cacheManager
     * @param DirectoryList $directoryList
     * @param Csv $csv
     * @param File $file
     * @param MessageManagerInterface $massageManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Emulation $emulation,
        Manager $cacheManager,
        DirectoryList $directoryList,
        Csv $csv,
        File $file,
        MessageManagerInterface $massageManager
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->emulation = $emulation;
        $this->cacheManager = $cacheManager;
        $this->directoryList = $directoryList;
        $this->csv = $csv;
        $this->file = $file;
        $this->massageManager = $massageManager;
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
        } catch (\DomainException $e) {
            if ($e->getMessage() == 'Missed phrase') {
                $guideUrl = 'https://magefan.com/magento-2-translation-extension/missed-phrase-error';

                $this->massageManager->addErrorMessage(__("Missed phrase. Please follow this guide to fix it - %1", $guideUrl));
            }
        } catch (\Exception $e) {

        } finally {
            $translationsData = $this->getTranslationsFromCsvFile($phrasesFilePath);

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
                foreach ($translationsData as $translationData) {
                    $key = $translationData['originalStr'];

                    if (!empty($key)) {
                        if (!isset($result[$key])) {
                            $module = $translationData['module'];

                            $module = implode(PHP_EOL, $module);
                            $pathToString = implode(PHP_EOL, $translationData['path_to_string']);

                            $result[$key] = [
                                'string' => $key,
                                'crc_string' => crc32($key),
                                'source' => 'phrases collector',
                                'module' => $module,
                                'path_to_string' => $pathToString
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
                        $originalStr = $value[0];
                        $result[$originalStr]['originalStr'] = $originalStr;

                        if (!empty($value[3])) {
                            $values = explode(self::MODULE_PATH_SEPARATOR, $value[3]);

                            if (count($values) == 2) {
                                $module = $values[0];
                                $pathToString = $values[1];

                                $pathToString = str_replace($this->directoryList->getRoot() . '/','', $pathToString);

                                $result[$originalStr]['path_to_string'][$pathToString] = $pathToString;
                            } else {
                                $module = $values;
                            }

                            $result[$originalStr]['module'][$module] = $module;
                        }
                    }
                }

                $this->file->deleteFile($csvFile);
            }
        } catch (FileSystemException $e) {

        }

        return $result;
    }
}
