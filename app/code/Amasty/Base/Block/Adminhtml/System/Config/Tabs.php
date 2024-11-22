<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config;

use Amasty\Base\Model\AmastyMenu\ActiveSolutionsProvider;
use Amasty\Base\Model\AmastyMenu\AmastyConfigItemsProvider;
use Amasty\Base\Model\AmastyMenu\ModuleTitlesResolver;
use Magento\Backend\Block\Template;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Framework\Serialize\Serializer\Json;

class Tabs extends Template
{
    /**
     * Constants for data array keys
     */
    public const BASE = 'base';
    public const SOLUTIONS = 'solutions';
    public const EXTENSIONS = 'extensions';

    public const ITEM_NAME = 'name';
    public const ITEM_CLASS = 'class';
    public const ITEM_URL = 'url';
    public const PLAN_LABEL = 'plan_label';
    public const IS_ACTIVE = 'is_active';
    public const SORT_ORDER = 'sort_order';

    /**
     * @var string
     */
    protected $_template = 'Amasty_Base::config/amasty_tabs.phtml';

    /**
     * @var AmastyConfigItemsProvider
     */
    private $configItemsProvider;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var string|null
     */
    private $currentSectionId;

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
        AmastyConfigItemsProvider $configItemsProvider,
        Json $serializer,
        ModuleTitlesResolver $moduleTitlesResolver,
        ActiveSolutionsProvider $activeSolutionsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configItemsProvider = $configItemsProvider;
        $this->serializer = $serializer;
        $this->currentSectionId = $this->getRequest()->getParam('section');
        $this->moduleTitlesResolver = $moduleTitlesResolver;
        $this->activeSolutionsProvider = $activeSolutionsProvider;
    }

    public function getConfigDataSerialized(): string
    {
        return $this->serializer->serialize($this->prepareConfigData());
    }

    private function prepareConfigData(): array
    {
        $result = $extensionsToRemove = [];

        $extensions = $this->getActiveExtensions();
        if (isset($extensions['Amasty_Base'])) {
            $result[self::BASE] = $extensions['Amasty_Base']; //separate from all
            unset($extensions['Amasty_Base']);
        }

        $solutions = $this->activeSolutionsProvider->get();
        $index = 0;
        foreach ($solutions as $solution) {
            $result[self::SOLUTIONS][$index][self::ITEM_NAME] = $this->moduleTitlesResolver->trimTitle(
                $solution['name'],
                $solution['solution_version'] ?? ''
            );
            $result[self::SOLUTIONS][$index][self::PLAN_LABEL] = $solution['solution_version'] ?? '';
            foreach ($solution['additional_extensions'] as $childExtension) {
                if (isset($extensions[$childExtension])) {
                    $result[self::SOLUTIONS][$index][self::EXTENSIONS][] = $extensions[$childExtension];
                    $extensionsToRemove[] = $childExtension;
                }
            }
            if (!empty($result[self::SOLUTIONS][$index][self::EXTENSIONS])) {
                $this->sortExtensions($result[self::SOLUTIONS][$index][self::EXTENSIONS]);
            } else {
                $result[self::SOLUTIONS][$index][self::EXTENSIONS] = [];
            }
            $index++;
        }

        $result[self::EXTENSIONS] = array_values(
            array_diff_key($extensions, array_flip(array_unique($extensionsToRemove)))
        );
        $this->sortExtensions($result[self::EXTENSIONS]);

        return $result;
    }

    private function getActiveExtensions(): array
    {
        $result = [];

        if ($amastyTab = $this->configItemsProvider->getAmastyConfigChildrenNode()) {
            foreach ($amastyTab as $section) {
                if (!$section->isVisible()) {
                    continue;
                }
                $result[current(explode('::', $section->getAttribute('resource')))] = [
                    self::ITEM_NAME => $this->getLabel($section),
                    self::ITEM_CLASS => $section->getClass(),
                    self::ITEM_URL => $this->getSectionUrl($section),
                    self::IS_ACTIVE => $section->getId() == $this->currentSectionId,
                    self::SORT_ORDER => $section->getAttribute('sortOrder')
                ];
            }
        }

        return $result;
    }

    private function getLabel(Section $section): string
    {
        return $this->_escaper->escapeHtml(__((string)$section->getLabel())->render());
    }

    private function getSectionUrl(Section $section): string
    {
        return $this->getUrl('*/*/*', ['_current' => true, 'section' => $section->getId()]);
    }

    private function sortExtensions(array &$extensions): void
    {
        usort($extensions, function ($a, $b) {
            return ($a[self::SORT_ORDER] < $b[self::SORT_ORDER]) ? -1 : 1;
        });
    }
}
