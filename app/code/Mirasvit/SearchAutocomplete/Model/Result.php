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



namespace Mirasvit\SearchAutocomplete\Model;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

class Result
{
    private static $isLayerCreated = false;

    private        $indexProvider;

    private        $layerResolver;

    private        $query;

    private        $config;

    private        $searchHelper;

    private        $queryFactory;

    private        $storeManager;

    public function __construct(
        IndexProvider         $indexProvider,
        LayerResolver         $layerResolver,
        QueryFactory          $queryFactory,
        ConfigProvider        $config,
        SearchHelper          $searchHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->indexProvider = $indexProvider;
        $this->layerResolver = $layerResolver;
        $this->queryFactory  = $queryFactory;
        $this->config        = $config;
        $this->searchHelper  = $searchHelper;
        $this->storeManager  = $storeManager;
    }

    public function init(): void
    {
        $this->query = $this->queryFactory->get();
        if (!self::$isLayerCreated) {
            try {
                $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
            } catch (\Exception $e) {
            } finally {
                self::$isLayerCreated = true;
            }
        }
    }

    /**
     * Convert all results to array
     */
    public function toArray(): array
    {
        $result = [
            'totalItems' => 0,
            'query'      => $this->query->getQueryText(),
            'indexes'    => [],
            'noResults'  => false,
            'urlAll'     => $this->searchHelper->getResultUrl($this->query->getQueryText()),
        ];

        if (strlen(trim($this->query->getQueryText())) < $this->config->getMinChars()) {
            $result['noResults'] = true;

            return $result;
        }

        $storeId = (int)$this->storeManager->getStore()->getId();

        $customInstances = [
            'magento_search_query',
            'magento_catalog_categoryproduct',
        ];

        $totalItems = 0;

        foreach ($this->indexProvider->getList() as $index) {
            if (!$index->isActive()) {
                continue;
            }

            $identifier = $index->getIdentifier();

            $instantProvider = $this->indexProvider->getInstantProvider($index);
            if (!$instantProvider) {
                continue;
            }

            $items = $instantProvider->getItems($storeId, $index->getLimit(), $this->getPageNum());
            $size  = $instantProvider->getSize($storeId);

            $result['indexes'][] = [
                'identifier'   => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                'title'        => $this->htmlEntityDecode((string)__($index->getTitle())),
                'position'     => (int)$index->getPosition(),
                'items'        => $items,
                'totalItems'   => $size,
                'pages'        => $this->getPaginationData($index->getLimit(), $size),
                'isShowTotals' => in_array($identifier, $customInstances) ? false : true,
            ];

            $totalItems += $size;

            if (!in_array($identifier, $customInstances)) {
                $result['totalItems'] += $size;
            }
        }

        if ($this->config->getAutocompleteLayout() == ConfigProvider::LAYOUT_2_COLUMNS) {
            foreach ($result['indexes'] as $key => $index) {
                if ($index['identifier'] == 'magento_catalog_product') {
                    $productFirst = $result['indexes'][$key];
                    unset($result['indexes'][$key]);
                    array_unshift($result['indexes'], $productFirst);
                }
            }
        }

        $result['textAll']   = $this->htmlEntityDecode((string)__('View all %1 results →', $result['totalItems']));
        $result['textEmpty'] = $this->htmlEntityDecode((string)__('Sorry, nothing has been found for "%1".', $result['query']));

        $result['noResults'] = $totalItems ? false : true;

        $this->query->setNumResults($result['totalItems']);

        return $result;
    }

    protected function getPageNum(): int
    {
        return (int)$this->getParam('p') ?? 1;
    }

    private function getPaginationData(int $limit, int $resultsQTY)
    {
        $pagesQty = ceil($resultsQTY / $limit);
        if ($pagesQty == 1) {
            return [];
        }
        $pages       = [];
        $currentPage = $this->getPageNum();
        for ($i = 1; $i <= $pagesQty; $i++) {
            $pages[] = ['isActive' => ($i == $currentPage ? true : false), 'label' => $i];
        }

        return $pages;
    }

    private function getParam(string $param)
    {
        if (filter_input(INPUT_GET, $param) != null) {
            return filter_input(INPUT_GET, $param);
        }

        return null;
    }

    private function htmlEntityDecode(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
