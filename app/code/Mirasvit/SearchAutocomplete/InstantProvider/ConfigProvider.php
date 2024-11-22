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
 * @package   mirasvit/module-search-ultimate
 * @version   2.1.8
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SearchAutocomplete\InstantProvider;

use Mirasvit\Search\Model\AbstractConfigProvider;

class ConfigProvider extends AbstractConfigProvider
{
    private $configData;

    private $storeId = 0;

    public function __construct(array $configData)
    {
        $this->configData = $configData;
    }

    public function getEngine(): string
    {
        $engine = (string)$this->configData["$this->storeId/engine"];
        if ($engine == 'opensearch') {
            $engine = 'elasticsearch7';
        }
        return $engine;
    }

    public function getIndexes(): array
    {
        return (array)$this->configData["$this->storeId/indexes"];
    }

    public function getIndexFields(string $indexIdentifier): array
    {
        return (array)$this->configData["$this->storeId/index/$indexIdentifier/fields"];
    }

    public function getIndexAttributes(string $indexIdentifier): array
    {
        return (array)$this->configData["$this->storeId/index/$indexIdentifier/attributes"];
    }

    public function getLimit(string $indexIdentifier): int
    {
        return (int)$this->configData["$this->storeId/index/$indexIdentifier/limit"];
    }

    public function getIndexName(string $indexIdentifier): string
    {
        $searchEngine = $this->getEngine();

        return (string)$this->configData["$this->storeId/$searchEngine"][$indexIdentifier];
    }

    public function getEngineConnection(): array
    {
        $searchEngine = $this->getEngine();

        return (array)$this->configData["$this->storeId/$searchEngine"]['connection'];
    }

    public function getIndexPosition(string $indexIdentifier): int
    {
        return (int)$this->configData["$this->storeId/index/$indexIdentifier/position"];
    }

    public function getIndexTitle(string $indexIdentifier): string
    {
        return (string)$this->configData["$this->storeId/index/$indexIdentifier/title"];
    }

    public function getTextAll(): string
    {
        return (string)$this->configData["$this->storeId/textAll"];
    }

    public function getTextEmpty(): string
    {
        return (string)$this->configData["$this->storeId/textEmpty"];
    }

    public function getUrlAll(): string
    {
        return (string)$this->configData["$this->storeId/urlAll"];
    }

    public function getLongTailExpressions(): array
    {
        return (array)$this->configData["$this->storeId/configuration/long_tail_expressions"];
    }

    public function getReplaceWords(): array
    {
        return (array)$this->configData["$this->storeId/configuration/replace_words"];
    }

    public function getWildcardMode(): string
    {
        return $this->configData["$this->storeId/configuration/wildcard"];
    }

    public function getMatchMode(): string
    {
        return $this->configData["$this->storeId/configuration/match_mode"];
    }

    public function getWildcardExceptions(): array
    {
        return $this->configData["$this->storeId/configuration/wildcard_exceptions"];
    }

    public function getSynonyms(array $terms, int $storeId): array
    {
        $synonyms     = [];
        $terms        = implode(' ', $terms);
        $initialQuery = $terms;
        $terms        = preg_replace('~\s~', ' ', trim($terms));
        $terms        = explode(' ', $terms);
        $terms[]      = $initialQuery;

        foreach ($this->configData["$this->storeId/synonymList"] as $synonymsGroup) {
            foreach (explode(',', $synonymsGroup) as $synonym) {
                foreach ($terms as $term) {
                    $synonym = trim($synonym);
                    $term    = trim($term);
                    if (mb_strtolower($synonym) == mb_strtolower($term)) {
                        if (isset($synonyms[$term])) {
                            $synonyms[$term] = array_merge($synonyms[$term], preg_split('/,/', $synonymsGroup));
                        } else {
                            $synonyms[$term] = preg_split('/,/', $synonymsGroup);
                        }
                    }
                }
            }
        }

        return $synonyms;
    }

    public function isStopword(string $term, int $storeId): bool
    {
        return in_array($term, $this->configData["$this->storeId/stopwordList"]);
    }

    public function applyStemming(string $term): string
    {
        if (substr($term, -2) === 'es') {
            $term = mb_substr($term, 0, -2);
        } elseif (substr($term, -1) === 's') {
            $term = mb_substr($term, 0, -1);
        }

        return $term;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getTypeaheadSuggestions(string $query): array
    {
        $suggestions = [];
        foreach ($this->configData["$this->storeId/typeahead"] as $groupKey => $suggestionsGroup) {
            if (substr($query, 0, 2) == $groupKey) {
                $suggestions = $suggestionsGroup;
                break;
            }
        }

        return $suggestions;
    }

    public function getAvailableBuckets(): array
    {
        return array_keys($this->configData["$this->storeId/buckets"]);
    }

    public function getBucketOptionsData(string $code, array $options): array
    {
        if (!isset($this->configData["$this->storeId/buckets"][$code])
            || !isset($this->configData["$this->storeId/buckets"][$code]['label'])) {
            return [];
        }

        $bucketData          = [];
        $bucketData['label'] = $this->configData["$this->storeId/buckets"][$code]['label'];
        $bucketData['code']  = $code;

        if ($code == 'price') {
            return $this->renderPriceFilter($code, $options, $bucketData);
        }

        if (!isset($this->configData["$this->storeId/buckets"][$code]['options'])) {
            return [];
        }

        $keys          = array_column($options, 'key');
        $activeOptions = array_intersect_key($this->configData["$this->storeId/buckets"][$code]['options'], array_flip($keys));

        foreach ($options as $option) {
            if ($option['doc_count'] == 0) {
                continue;
            }

            if ($code == 'category_ids' && (int)$option['key'] == 2) {
                continue;
            }

            if (!isset($activeOptions[$option['key']])) {
                continue;
            }

            $bucketData['buckets'][] = [
                'key'    => $option['key'],
                'label'  => $activeOptions[$option['key']],
                'count'  => $option['doc_count'],
                'filter' => json_encode([$code => $option['key']]),
            ];
        }

        if (empty($bucketData['buckets'])) {
            return [];
        }

        return $bucketData;
    }

    public function getActiveFilters(): array
    {
        $filters = [];
        if (filter_input(INPUT_GET, 'filters', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY)) {
            $filters = array_merge($filters, filter_input(INPUT_GET, 'filters', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY));
        }

        return $filters;
    }

    public function getProductsPerPage(): int
    {
        return (int)$this->configData["$this->storeId/productsPerPage"];
    }

    public function getLayeredNavigationPosition(): string
    {
        return (string)$this->configData["$this->storeId/displayFilters"];
    }

    public function getPaginationPosition(): string
    {
        return (string)$this->configData["$this->storeId/pagination"];
    }

    private function renderPriceFilter(string $code, array $options, array $bucketData): array
    {
        if (empty($options)) {
            return [];
        }

        $prices = array_column($options, 'key');
        asort($prices);
        $minPrice = round(min($prices), -1) - 10;
        $minPrice = ($minPrice > 0) ? $minPrice : 0;
        $maxPrice = round(max($prices), -1) + 10;

        $rangeLimits = range($minPrice, $maxPrice, 10);
        $ranges      = [];


        $currencySymbol = $this->configData["$this->storeId/currencySymbol"];

        foreach ($rangeLimits as $key => $rangeLimit) {
            if (!isset($rangeLimits[$key + 1])) {
                continue;
            }

            $minLimit   = $rangeLimit;
            $maxLimit   = $rangeLimits[$key + 1];
            $rangeKey   = $minLimit . '_' . $maxLimit;
            $rangeLabel = $currencySymbol . $minLimit . ' - ' . $currencySymbol . $maxLimit;
            $count      = 0;

            foreach ($options as $option) {
                if (round($option['key']) >= $minLimit && round($option['key']) <= $maxLimit) {
                    $count += $option['doc_count'];
                }
            }

            if ($count) {
                $bucketData['buckets'][] = [
                    'key'    => json_encode(['gte' => $minLimit, 'lte' => $maxLimit]),
                    'label'  => $rangeLabel,
                    'count'  => $count,
                    'filter' => json_encode([$code => ['gte' => $minLimit, 'lte' => $maxLimit]]),
                ];
                $ranges[$rangeKey]       = $count;
            }
        }

        return $bucketData;
    }
}
