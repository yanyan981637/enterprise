<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Model;

use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Filesystem\DirectoryList;

class JsTranslation
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
     * @var File
     */
    private $driverFile;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Emulation $emulation
     * @param Manager $cacheManager
     * @param DirectoryList $directoryList
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Emulation $emulation,
        Manager $cacheManager,
        DirectoryList $directoryList
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->emulation = $emulation;
        $this->cacheManager = $cacheManager;
        $this->directoryList = $directoryList;
    }

    /**
     * Search translations in pub/static/*\js-translation.json
     *
     * @return array|false
     */
    public function getData()
    {
        $it = new \RecursiveDirectoryIterator($this->directoryList->getPath('static'));
        $keys = [];
        $source = [];
        foreach (new \RecursiveIteratorIterator($it) as $file) {
            if ($file->getFilename() == "js-translation.json") {
                $path = $file->getPathname();

                $tmp = json_decode(file_get_contents($path), true);

                foreach ($tmp as $key => $value) {
                    if (!empty($key)) {
                        $keys[] = $key;
                        $source[$key] = 'js-translation.json';
                    }
                }
            }
        }

        if (!$keys) {
            return [];
        }
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
                            'source' => $source[$key]
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
