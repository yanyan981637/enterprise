<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml;

use Amasty\Base\Model\AmastyMenu\ActiveSolutionsProvider;
use Amasty\Base\Model\AmastyMenu\ModuleTitlesResolver;
use Amasty\Base\Model\Feed\ExtensionsProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Model\ModuleListProcessor;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Serialize\Serializer\Json;

class Extensions extends Field
{
    public const SEO_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=ext_list';

    /**
     * Constants for keys of data array
     */
    public const NAME = 'name';
    public const VERSION = 'version';
    public const LAST_VERSION = 'last_version';
    public const HAS_UPDATE = 'has_update';
    public const UPDATE_URL = 'update_url';
    public const IS_SOLUTION = 'is_solution';
    public const PLAN_LABEL = 'plan_label';
    public const UPGRADE_URL = 'upgrade_url';

    /**
     * @var string
     */
    protected $_template = 'Amasty_Base::modules.phtml';

    /**
     * @var ModuleListProcessor
     */
    private $moduleListProcessor;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @var ExtensionsProvider
     */
    private $extensionsProvider;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ModuleTitlesResolver
     */
    private $moduleTitlesResolver;

    /**
     * @var ActiveSolutionsProvider
     */
    private $activeSolutionsProvider;

    public function __construct(
        Template\Context $context,
        ModuleListProcessor $moduleListProcessor,
        ModuleInfoProvider $moduleInfoProvider,
        ExtensionsProvider $extensionsProvider,
        Json $serializer,
        ModuleTitlesResolver $moduleTitlesResolver,
        ActiveSolutionsProvider $activeSolutionsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleListProcessor = $moduleListProcessor;
        $this->moduleInfoProvider = $moduleInfoProvider;
        $this->extensionsProvider = $extensionsProvider;
        $this->serializer = $serializer;
        $this->moduleTitlesResolver = $moduleTitlesResolver;
        $this->activeSolutionsProvider = $activeSolutionsProvider;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    public function getModulesDataJson(): string
    {
        return $this->serializer->serialize($this->getModulesData());
    }

    public function isOriginMarketplace(): bool
    {
        return $this->moduleInfoProvider->isOriginMarketplace();
    }

    public function getSeoparams(): string
    {
        return !$this->isOriginMarketplace() ? self::SEO_PARAMS : '';
    }

    private function getModulesData(): array
    {
        $result = [];

        $modulesList = array_merge(...array_values($this->moduleListProcessor->getModuleList()));
        $allSolutions = $this->extensionsProvider->getAllSolutionsData();
        $activeSolutions = $this->activeSolutionsProvider->get();

        foreach ($modulesList as $module) {
            $moduleCode = $module['code'];
            $isSolution = isset($allSolutions[$moduleCode]);
            if ($isSolution && !isset($activeSolutions[$moduleCode])) {
                continue;
            }
            $planLabel = $isSolution ? $activeSolutions[$moduleCode]['solution_version'] : '';
            $item = [];

            $item[self::NAME] = $this->moduleTitlesResolver->trimTitle(
                htmlspecialchars_decode($module['description']),
                $planLabel
            );
            $item[self::VERSION] = $module['version'];
            $item[self::LAST_VERSION] = $module['lastVersion'];
            $item[self::HAS_UPDATE] = $module['hasUpdate'];
            $item[self::UPDATE_URL] = $this->prepareUpdateUrl($module['url']);
            $item[self::IS_SOLUTION] = $isSolution;
            $item[self::PLAN_LABEL] = $planLabel;
            $item[self::UPGRADE_URL] = $isSolution ? $activeSolutions[$moduleCode]['upgrade_url'] : '';

            $result[] = $item;
        }
        usort($result, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $result;
    }

    private function prepareUpdateUrl(string $moduleUrl): string
    {
        $seo = !$this->isOriginMarketplace() ? self::SEO_PARAMS : '';
        $anchor = $this->isOriginMarketplace() ? '#product.info.details.release_notes' : '#changelog';

        return $moduleUrl . $seo . $anchor;
    }
}
