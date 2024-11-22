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
 * @package   mirasvit/module-seo-filter
 * @version   1.3.2
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Service;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture;
use Magento\Eav\Model\Entity\Attribute\Option as AttributeOption;
use Magento\Framework\Module\Manager as ModuleManager;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\Context;
use Mirasvit\SeoFilter\Repository\RewriteRepository;
use Mirasvit\SeoFilter\Model\RewriteFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RewriteService
{
    /** @var array */
    private static $activeFilters = null;

    private        $rewriteRepository;

    private        $layerResolver;

    private        $context;

    private        $labelService;

    private        $configProvider;

    private        $moduleManager;

    private        $cacheService;

    private        $rewriteFactory;

    private        $countryOfManufactureSource;

    public function __construct(
        RewriteRepository $rewriteRepository,
        LayerResolver $layerResolver,
        Context $context,
        LabelService $labelService,
        ConfigProvider $configProvider,
        ModuleManager $moduleManager,
        RewriteFactory $rewriteFactory,
        CacheService $cacheService,
        Countryofmanufacture $countryOfManufactureSource
    ) {
        $this->rewriteRepository = $rewriteRepository;
        $this->layerResolver     = $layerResolver;
        $this->context           = $context;
        $this->labelService      = $labelService;
        $this->configProvider    = $configProvider;
        $this->moduleManager     = $moduleManager;
        $this->rewriteFactory    = $rewriteFactory;
        $this->cacheService      = $cacheService;

        $this->countryOfManufactureSource = $countryOfManufactureSource;
    }

    public function getAttributeRewrite(string $attributeCode, ?int $storeId = null, bool $useCache = true): ?RewriteInterface
    {
        if ($storeId === null) {
            $storeId = $this->context->getStoreId();
        }

        if ($useCache && $rewriteData = $this->cacheService->getCache('getAttributeRewrite', [$attributeCode, $storeId])) {
            $rewrite = $this->rewriteFactory->create();
            $rewrite->setData($rewriteData);

            return $rewrite;
        }

        /** @var RewriteInterface $rewrite */
        $rewrite = $this->rewriteRepository->getCollection()
            ->addFieldToFilter(RewriteInterface::ATTRIBUTE_CODE, $attributeCode)
            ->addFieldToFilter(RewriteInterface::OPTION, ['null' => true])
            ->addFieldToFilter(RewriteInterface::STORE_ID, $storeId)
            ->getFirstItem();

        if ($rewrite->getId()) {
            $this->cacheService->setCache('getAttributeRewrite', [$attributeCode, $storeId], [$rewrite->getData()]);

            return $rewrite;
        }

        $rewrite = $this->createNewAttributeRewrite($attributeCode, $storeId);

        return $rewrite ? $rewrite : null;
    }

    public function getAttributeRewriteByAlias(string $alias, ?int $storeId = null, bool $useCache = true): ?RewriteInterface
    {
        if ($storeId === null) {
            $storeId = $this->context->getStoreId();
        }

        if ($useCache && $rewriteData = $this->cacheService->getCache('getAttributeRewriteByAlias', [$alias, $storeId])) {
            $rewrite = $this->rewriteFactory->create();
            $rewrite->setData($rewriteData);

            return $rewrite;
        }
        /** @var RewriteInterface $rewrite */
        $rewrite = $this->rewriteRepository->getCollection()
            ->addFieldToFilter(RewriteInterface::REWRITE, $alias)
            ->addFieldToFilter(RewriteInterface::OPTION, ['null' => true])
            ->addFieldToFilter(RewriteInterface::STORE_ID, $storeId)
            ->getFirstItem();

        if ($rewrite->getId()) {
            $this->cacheService->setCache('getAttributeRewriteByAlias', [$alias, $storeId], [$rewrite->getData()]);

            return $rewrite;
        }

        return null;
    }

    public function getOptionRewriteByAlias(string $alias, ?int $storeId = null, bool $useCache = true): ?RewriteInterface
    {
        if ($storeId === null) {
            $storeId = $this->context->getStoreId();
        }

        if ($useCache && ($rewriteData = $this->cacheService->getCache('getOptionRewriteByAlias', [$alias, $storeId]))) {
            $rewrite = $this->rewriteFactory->create();
            $rewrite->setData($rewriteData);

            return $rewrite;
        }

        /** @var RewriteInterface $rewrite */
        $rewrite = $this->rewriteRepository->getCollection()
            ->addFieldToFilter(RewriteInterface::REWRITE, $alias)
            ->addFieldToFilter(RewriteInterface::OPTION, ['notnull' => true])
            ->addFieldToFilter(RewriteInterface::STORE_ID, $storeId)
            ->getFirstItem();

        if ($rewrite->getId()) {
            $this->cacheService->setCache('getOptionRewriteByAlias', [$alias, $storeId], [$rewrite->getData()]);

            return $rewrite;
        }

        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getOptionRewrite(string $attributeCode, string $filterValue, ?int $storeId = null, bool $useCache = true): ?RewriteInterface
    {
        if ($attributeCode == ConfigProvider::FILTER_RATING) {
            return $this->getRatingFilterRewrite((int)$filterValue);
        } elseif ($attributeCode == ConfigProvider::FILTER_STOCK) {
            return $this->getStockFilterRewrite((int)$filterValue);
        } elseif ($attributeCode == ConfigProvider::FILTER_SALE) {
            return $this->getSaleFilterRewrite((int)$filterValue);
        } elseif ($attributeCode == ConfigProvider::FILTER_NEW) {
            return $this->getNewFilterRewrite((int)$filterValue);
        }

        if ($storeId === null) {
            $storeId = $this->context->getStoreId();
        }

        if ($useCache && ($rewriteData = $this->cacheService->getCache('getOptionRewrite', [$attributeCode, $filterValue, $storeId]))) {
            $rewrite = $this->rewriteFactory->create();
            $rewrite->setData($rewriteData);

            return $rewrite;
        }

        /** @var RewriteInterface $rewrite */
        $rewrite = $this->rewriteRepository->getCollection()
            ->addFieldToFilter(RewriteInterface::ATTRIBUTE_CODE, $attributeCode)
            ->addFieldToFilter(RewriteInterface::OPTION, $filterValue)
            ->addFieldToFilter(RewriteInterface::STORE_ID, $storeId)
            ->getFirstItem();

        if ($rewrite->getId()) {
            $this->cacheService->setCache('getOptionRewrite', [$attributeCode, $filterValue, $storeId], [$rewrite->getData()]);

            return $rewrite;
        }

        $rewrite = $this->createNewOptionRewrite($attributeCode, $filterValue, $storeId);

        return $rewrite ? $rewrite : null;
    }

    public function getActiveFilters(): array
    {
        if (self::$activeFilters === null) {
            self::$activeFilters = [];

            $layer = $this->layerResolver->get();
            foreach ($layer->getState()->getFilters() as $item) {
                $filter = $item->getFilter();

                if (is_array($item->getData('value'))) {
                    $filterValue = implode(ConfigProvider::SEPARATOR_FILTER_VALUES, $item->getData('value'));
                } else {
                    $filterValue = (string)$item->getData('value');
                }

                if ($filter->getData('attribute_model')) {
                    $attributeCode = $filter->getAttributeModel()->getAttributeCode();
                } else {
                    $attributeCode = $filter->getRequestVar();
                }

                if (!is_array($filterValue)) {
                    $filterValue = explode(ConfigProvider::SEPARATOR_FILTER_VALUES, $filterValue);
                }
                if ($attributeCode === 'price') {
                    $filterValue = [implode('-', $filterValue)];
                }

                foreach ($filterValue as $value) {
                    self::$activeFilters[$attributeCode][$value] = $value;
                }
            }
        }

        return self::$activeFilters;
    }

    private function getStockFilterRewrite(int $stockValue): RewriteInterface
    {
        $rewrite = $stockValue === 2 ? ConfigProvider::LABEL_STOCK_IN : ConfigProvider::LABEL_STOCK_OUT;

        return $this->makeStaticRewrite($rewrite);
    }

    private function getSaleFilterRewrite(int $value): RewriteInterface
    {
        $rewrite = ConfigProvider::FILTER_SALE . '_';

        $rewrite .= $value ? 'yes' : 'no';

        return $this->makeStaticRewrite($rewrite);
    }

    private function getNewFilterRewrite(int $value): RewriteInterface
    {
        $rewrite = ConfigProvider::FILTER_NEW . '_';

        $rewrite .= $value ? 'yes' : 'no';

        return $this->makeStaticRewrite($rewrite);
    }

    private function getRatingFilterRewrite(int $ratingValue): RewriteInterface
    {
        switch ($ratingValue) {
            case 1:
                $rewrite = ConfigProvider::LABEL_RATING_1;
                break;
            case 2:
                $rewrite = ConfigProvider::LABEL_RATING_2;
                break;
            case 3:
                $rewrite = ConfigProvider::LABEL_RATING_3;
                break;
            case 4:
                $rewrite = ConfigProvider::LABEL_RATING_4;
                break;
            case 5:
                $rewrite = ConfigProvider::LABEL_RATING_5;
                break;
            default:
                $rewrite = ConfigProvider::LABEL_RATING_5;
        }

        return $this->makeStaticRewrite($rewrite);
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    private function createNewOptionRewrite(string $attributeCode, string $filterValue, ?int $storeId = null): ?RewriteInterface
    {
        $attribute = $this->context->getAttribute($attributeCode);

        if (!$attribute) {
            return null;
        }

        $attributeId = (int)$attribute->getId();

        $attributeOption = $this->context->getAttributeOption($attributeId, (int)$filterValue);

        if ($groupedLabel = $this->getGroupedOptionLabel($filterValue, $storeId)) {
            $label = $this->labelService->createLabel($attributeCode, $groupedLabel, $attributeOption);
        } elseif ($this->context->isDecimalAttribute($attributeCode)) {
            $label = $this->labelService->createLabel($attributeCode, $filterValue, $attributeOption);
        } elseif ($attributeCode == 'country_of_manufacture') {
            $allCountries = $this->countryOfManufactureSource->getAllOptions();

            $label = '';

            foreach ($allCountries as $country) {
                if ($country['value'] == $filterValue) {
                    $label = $this->labelService->createLabel($attributeCode, $country['label'], $attributeOption);
                    break;
                }
            }

            if (!$label) {
                $label = $this->labelService->createLabel(
                    $attributeCode,
                    $attributeCode . ' ' . $filterValue,
                    $attributeOption)
                ;
            }
        } elseif (
            $attributeOption
            && ($optionValue = $this->resolveAttributeOptionValue($attributeOption))
        ) {
            $label = $this->labelService->createLabel($attributeCode, $optionValue, $attributeOption);
        } elseif ((int)$filterValue === 1 || $filterValue === '1') {
            $label = $attributeCode;
        } elseif ((int)$filterValue === 0 || $filterValue === '0') {
            $label = $attributeCode . '_no';
        } elseif ($attributeCode == 'category_ids') {
            $label = $this->labelService->createLabel($attributeCode, $filterValue, $attributeOption);
        } else {
            $label = $this->labelService->createLabel($attributeCode, $attributeCode . ' ' . $filterValue, $attributeOption);
        }

        if ($storeId === null) {
            $storeId = $this->context->getStoreId();
        }

        if ($this->configProvider->getUrlFormat() === ConfigProvider::URL_FORMAT_OPTIONS) {
            $label = $this->labelService->uniqueLabel($label, $storeId);
        }

        $rewrite = $this->rewriteRepository->create();
        $rewrite->setAttributeCode($attributeCode)
            ->setOption($filterValue)
            ->setRewrite($label)
            ->setStoreId($storeId);

        $this->rewriteRepository->save($rewrite);

        return $rewrite;
    }

    private function getGroupedOptionLabel(string $optionValue, int $storeId): ?string
    {
        if (!$this->moduleManager->isEnabled('Mirasvit_LayeredNavigation')) {
            return null;
        }

        try {
            $groupRepository = CompatibilityService::getObjectManager()
                ->get('\Mirasvit\LayeredNavigation\Repository\GroupRepository');
        } catch (\Exception $e) {
            return null;
        }

        if ($group = $groupRepository->getByCode($optionValue)) {
            return $group->getLabelByStoreId($storeId);
        }

        return null;
    }

    private function createNewAttributeRewrite(string $attributeCode, ?int $storeId = null): ?RewriteInterface
    {
        $attribute = $this->context->getAttribute($attributeCode);

        if (!$attribute) {
            return null;
        }

        if ($storeId === null) {
            $storeId = $this->context->getStoreId();
        }

        $urlRewrite = $this->labelService->uniqueLabel($attributeCode == 'category_ids' ? 'category' : $attributeCode, $storeId);

        $rewrite = $this->rewriteRepository->create();
        $rewrite->setAttributeCode($attributeCode)
            ->setRewrite($urlRewrite)
            ->setStoreId($storeId);

        $this->rewriteRepository->save($rewrite);

        return $rewrite;
    }

    private function makeStaticRewrite(string $value): RewriteInterface
    {
        $rewrite = $this->rewriteRepository->create();
        $rewrite->setRewrite($value);

        return $rewrite;
    }

    private function resolveAttributeOptionValue(AttributeOption $attributeOption): ?string
    {
        $value = trim($attributeOption->getValue());

        if (!$value) {
            $value = trim((string)$attributeOption->getData('store_default_value'));
        }

        if (!$value) {
            $value = trim((string)$attributeOption->getData('default_value'));
        }

        return $value ?: null;
    }
}
