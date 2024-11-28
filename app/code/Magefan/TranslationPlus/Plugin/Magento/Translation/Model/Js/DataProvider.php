<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Plugin\Magento\Translation\Model\Js;

use Magento\Framework\App\State;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Translation\Model\Js\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Framework\App\Utility\Files as UtilityFiles;
use Magefan\Translation\Model\Config as MfConfig;

class DataProvider
{
    /**
     * @var array
     */
    protected static $_cache = [];

    /**
     * @var Config
     */
    private  $config;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ReadFactory
     */
    private $fileReadFactory;

    /**
     * @var RendererInterface
     */
    private $translate;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ThemePackageList
     */
    private $themePackageList;

    /**
     * @var UtilityFiles
     */
    private $filesUtility;

    /**
     * @var MfConfig
     */
    private $mfConfig;

    /**
     * @param State $appState
     * @param Config $config
     * @param ReadFactory $fileReadFactory
     * @param RendererInterface $translate
     * @param ComponentRegistrar $componentRegistrar
     * @param DirSearch $dirSearch
     * @param ThemePackageList $themePackageList
     * @param UtilityFiles|null $filesUtility
     */
    public function __construct(
        State $appState,
        Config $config,
        ReadFactory $fileReadFactory,
        RendererInterface $translate,
        ComponentRegistrar $componentRegistrar,
        DirSearch $dirSearch,
        ThemePackageList $themePackageList,
        MfConfig $mfConfig,
        Files $filesUtility = null
    ) {
        $this->appState = $appState;
        $this->config = $config;
        $this->fileReadFactory = $fileReadFactory;
        $this->translate = $translate;
        $this->componentRegistrar = $componentRegistrar;
        $this->themePackageList = $themePackageList;
        $this->mfConfig = $mfConfig;

        $this->filesUtility = (null !== $filesUtility) ?
            $filesUtility : new \Magento\Framework\App\Utility\Files(
                $componentRegistrar,
                $dirSearch,
                $themePackageList
            );
    }

    /**
     * This function similar with Magento\Translation\Model\Js\DataProvider::getData
     * @param \Magento\Translation\Model\Js\DataProvider $subject
     * @param $dictionary
     * @param $themePath
     * @return mixed
     * @throws LocalizedException
     */
    public function afterGetData(
        \Magento\Translation\Model\Js\DataProvider $subject,
        $dictionary,
        $themePath
    ) {
        if (!$this->mfConfig->isEnabled()) {
            return $dictionary;
        }

        $areaCode = $this->appState->getAreaCode();
        $files = array_merge(
            $this->getStaticPhtmlFiles('base', $themePath),
            $this->getStaticPhtmlFiles($areaCode, $themePath)
        );

        foreach ($files as $filePath) {
            $read = $this->fileReadFactory->create($filePath[0], \Magento\Framework\Filesystem\DriverPool::FILE);
            $content = $read->readAll();
            foreach ($this->getPhrases($content) as $phrase) {
                if (isset($dictionary[$phrase])) {
                    continue;
                }
                try {
                    $translatedPhrase = $this->translate->render([$phrase], []);
                    if ($phrase != $translatedPhrase) {
                        $dictionary[$phrase] = $translatedPhrase;
                    }
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        __('Error while translating phrase "%s" in file %s.', $phrase, $filePath[0]),
                        $e
                    );
                }
            }
        }
        ksort($dictionary);

        return $dictionary;
    }

    /**
     * This function similar with Magento\Framework\App\Utility\Files::getStaticHtmlFiles
     * @param $area
     * @param $themePath
     * @param $namespace
     * @param $module
     * @return array|mixed
     */
    private function getStaticPhtmlFiles($area = '*', $themePath = '*/*', $namespace = '*', $module = '*')
    {
        $key = $area . $themePath . $namespace . $module . BP;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $moduleWebPaths = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $keyInfo = explode('_', $moduleName);
            if ($keyInfo[0] == $namespace || $namespace == '*') {
                if ($keyInfo[1] == $module || $module == '*') {
                    $moduleWebPaths[] = $moduleDir . "/view/{$area}/templates";
                }
            }
        }
        $themePaths = $this->getThemePaths($area, $namespace . '_' . $module, '/templates');
        $files = UtilityFiles::getFiles(
            array_merge(
                $themePaths,
                $moduleWebPaths
            ),
            '*.phtml'
        );
        $result = UtilityFiles::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * This function similar with Magento\Framework\App\Utility\Files::getThemePaths
     * @param $area
     * @param $module
     * @param $subFolder
     * @return array
     */
    private function getThemePaths($area, $module, $subFolder)
    {
        $themePaths = [];
        foreach ($this->themePackageList->getThemes() as $theme) {
            if ($area == '*' || $theme->getArea() === $area) {
                $themePaths[] = $theme->getPath() . $subFolder;
                $themePaths[] = $theme->getPath() . "/{$module}" . $subFolder;
            }
        }
        return $themePaths;
    }

    /**
     * This function similar with Magento\Translation\Model\Js\DataProvider::getPhrases
     * @param $content
     * @return array
     * @throws LocalizedException
     */
    private function getPhrases($content)
    {
        $phrases = [];
        foreach ($this->config->getPatterns() as $pattern) {
            $concatenatedContent = preg_replace('~(["\'])\s*?\+\s*?\1~', '', $content);
            $result = preg_match_all($pattern, $concatenatedContent, $matches);

            if ($result) {
                if (isset($matches[2])) {
                    foreach ($matches[2] as $match) {
                        $phrases[] = $match !== null ? str_replace(["\'", '\"'], ["'", '"'], $match) : '';
                    }
                }
            }
            if (false === $result) {
                throw new LocalizedException(
                    __('Error while generating js translation dictionary: "%s"', error_get_last())
                );
            }
        }
        return $phrases;
    }
}
