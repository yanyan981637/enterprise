<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Service;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Mirasvit\LayeredNavigation\Model\Config\ConfigTrait;
use Mirasvit\LayeredNavigation\Model\Config\StateBarConfigProvider;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;

class FilterService
{
    private static $activeFilters = null;

    private        $layerResolver;

    private        $configProvider;

    private        $stateBarConfigProvider;

    public function __construct(
        LayerResolver $layerResolver,
        ConfigProvider $configProvider,
        StateBarConfigProvider $stateBarConfigProvider
    ) {
        $this->layerResolver          = $layerResolver->get();
        $this->configProvider         = $configProvider;
        $this->stateBarConfigProvider = $stateBarConfigProvider;
    }

    /** @return Item[] */
    public function getActiveFilters(): array
    {
        if (self::$activeFilters === null) {
            self::$activeFilters = $this->layerResolver->getState()->getFilters();
        }

        return (self::$activeFilters === null || !is_array(self::$activeFilters)) ? [] : self::$activeFilters;
    }

    public function isFilterItemChecked(Item $filterItem, bool $multiselect): bool
    {
        return $this->isMultiselectFilterChecked($filterItem);
    }

    public function isFilterCheckedSwatch(string $attributeCode, string $option): bool
    {
            $activeFilters = $this->getActiveFilters();
            foreach ($activeFilters as $key => $filter) {
                if ($filter->getFilter()->getRequestVar() == $attributeCode
                    && ($filter->getValueString() == $option
                        || $this->inOneRowExist($filter->getValueString(), $option))
                ) {
                    return true;
                }
            }

        return false;
    }

    public function getFilterUniqueValue(AbstractFilter $filter): string
    {
        return $filter->getRequestVar() . '_' . $filter->getValue();
    }

    private function isMultiselectFilterChecked(Item $filterItem): bool
    {
        $activeFilters  = $this->getActiveFilters();
        $attributeCode  = $filterItem->getFilter()->getRequestVar();
        $attributeValue = (string)$filterItem->getValueString();

        foreach ($activeFilters as $key => $filter) {
            $values = explode(',', (string)$filter->getValueString());

            if ($filter->getFilter()->getRequestVar() == $attributeCode) {
                if (in_array($attributeValue, $values, true) || $attributeValue === implode('-', $values)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function inOneRowExist(string $filterValueString, string $option): bool
    {
        if ($this->stateBarConfigProvider->isFilterClearBlockInOneRow()) {
            $filterValueStringArray = explode(',', $filterValueString);
            if (array_search($option, $filterValueStringArray) !== false) {
                return true;
            }
        }

        return false;
    }
}
