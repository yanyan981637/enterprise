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


namespace Mirasvit\LayeredNavigationLiveSearch\Plugin\LiveSearch;


use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\CatalogDataExporter\Model\Query\ProductCategoryDataQuery;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\DataExporter\Model\Indexer\FeedIndexMetadata;
use Magento\DataExporter\Model\Indexer\FeedIndexProcessorCreateUpdate;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver as TableResolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;
use Mirasvit\LayeredNavigation\Model\Layer\Filter\AbstractFilter;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddExtraFiltersMetadataPlugin
{
    private $extraFilterConfigProvider;

    private $resource;

    private $storeManager;

    private $stockState;

    private $objectManager;

    private $categoryRepository;

    private $tableResolver;

    private $additionalFilters;

    private $dataMappers;

    public function __construct(
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockState,
        ObjectManagerInterface $objectManager,
        CategoryRepositoryInterface $categoryRepository,
        TableResolver $tableResolver,
        array $additionalFilters,
        array $dataMappers
    ) {
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
        $this->resource                  = $resource;
        $this->storeManager              = $storeManager;
        $this->stockState                = $stockState;
        $this->objectManager             = $objectManager;
        $this->categoryRepository        = $categoryRepository;
        $this->tableResolver             = $tableResolver;
        $this->additionalFilters         = $additionalFilters;
        $this->dataMappers               = $dataMappers;
    }

    /**
     * @param FeedIndexProcessorCreateUpdate $subject
     * @param mixed $output
     * @param FeedIndexMetadata $metadata
     * @return mixed
     */
    public function afterFullReindex(FeedIndexProcessorCreateUpdate $subject, $output, FeedIndexMetadata $metadata)
    {
        switch($metadata->getFeedName()) {
            case 'productAttributes':
                $this->addExtraFiltersAsAttributesToMetadata($metadata);

                break;
            case 'products':
                foreach ($this->storeManager->getStores() as $store) {
                    if ($store->getId() == 0) {
                        continue;
                    }

                    $this->addFilterValuesToProductMetadata($metadata, $store);
                }

                break;
        }

        return $output;
    }

    private function addExtraFiltersAsAttributesToMetadata(FeedIndexMetadata $metadata): void
    {
        $query = "SELECT id FROM " . $this->resource->getTableName($metadata->getFeedTableName()) . " ORDER BY id DESC LIMIT 1";

        $lastId = $this->resource->getConnection()
            ->query($query)
            ->fetchAll()[0]['id'];

        $newId = (int)$lastId + 10;

        $stores = $this->storeManager->getStores();

        foreach ($this->additionalFilters as $code => $filter) {
            if ($code == 'search') { // ignore search filter for now
                continue;
            }

            foreach ($stores as $store) {
                if ($store->getId() == 0) {
                    continue;
                }

               if ($filterParam = $this->getFilterParamName($code)) {
                   $this->addPseudoAttributeMetadata(
                       $metadata->getFeedTableName(),
                       (string)$newId,
                       $filterParam,
                       $this->getFilterLabel($code),
                       $this->storeManager->getGroup($store->getStoreGroupId())->getCode(),
                       $this->storeManager->getWebsite($store->getWebsiteId())->getCode(),
                       $store->getCode()
                   );
               }
            }

            $newId++;
        }
    }

    private function getFilterParamName(string $code): ?string
    {
        $filterCode = strtoupper($code) . '_FILTER';
        $reflection = new \ReflectionClass(ExtraFilterConfigProvider::class);

        return $reflection->getConstant($filterCode) ?: null;
    }

    private function getFilterLabel(string $filterCode): string
    {
        $method = 'get' . $this->extraFilterConfigProvider->transformToMethod($filterCode) . 'FilterLabel';

        if (!method_exists($this->extraFilterConfigProvider, $method)) {
            throw new LocalizedException(__('Filter type "%1" does not exist', $filterCode));
        }

        return $this->extraFilterConfigProvider->{$method}();
    }

    private function addPseudoAttributeMetadata(
        string $tableName,
        string $id,
        string $code,
        string $label,
        string $storeCode,
        string $websiteCode,
        string $storeViewCode
    ): void {
        $keys = ['id', 'store_view_code', 'feed_data', 'is_deleted'];

        $isRatingFilter = $code == ExtraFilterConfigProvider::RATING_FILTER;

        $feedData = [
            "id"                   => $id,
            "storeCode"            => $storeCode,
            "websiteCode"          => $websiteCode,
            "storeViewCode"        => $storeViewCode,
            "attributeCode"        => $code,
            "attributeType"        => "catalog_product",
            "dataType"             => "int",
            "multi"                => false,
            "label"                => $label,
            "frontendInput"        => $isRatingFilter ? "select" : "boolean",
            "required"             => false,
            "unique"               => false,
            "global"               => false,
            "visible"              => true,
            "searchable"           => false,
            "filterable"           => true,
            "visibleInCompareList" => false,
            "visibleInListing"     => false,
            "sortable"             => false,
            "visibleInSearch"      => false,
            "filterableInSearch"   => true,
            "searchWeight"         => 1,
            "usedForRules"         => false,
            "boolean"              => $isRatingFilter ? false : true,
            "systemAttribute"      => true,
            "numeric"              => $isRatingFilter ? true : false,
            "attributeOptions"     => $isRatingFilter ? [0,1,2,3,4,5] : null
        ];

        $this->resource->getConnection()->insertOnDuplicate(
            $this->resource->getTableName($tableName),
            [
                'id'              => $id,
                'store_view_code' => $storeViewCode,
                'feed_data'       => SerializeService::encode($feedData),
                'is_deleted'      => 0
            ],
            $keys
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function addFilterValuesToProductMetadata(FeedIndexMetadata $metadata, StoreInterface $store): void
    {
        $keys        = null;
        $rows        = [];
        $ids         = [];
        $selectQuery = "SELECT * FROM " . $this->resource->getTableName($metadata->getFeedTableName())
            . " WHERE store_view_code = '" . $store->getCode() . "'";

        $productsMetadata = $this->resource->getConnection()->query($selectQuery);

        while ($row = $productsMetadata->fetch()) {
            if (!$keys) {
                $keys = array_keys($row);
            }

            $row['feed_data']  = SerializeService::decode($row['feed_data']);
            $rows[$row['sku']] = $row;
            $ids[]             = $row['id'];
        }

        $filterValuesPerProduct = [];

        foreach ($this->additionalFilters as $code => $filter) { // all but search and stock filters
            if (!isset($this->dataMappers[$code]) || $code == 'stock') {
                continue;
            }

            $dataMapper = $this->objectManager->get($this->dataMappers[$code]);
            $values     = $this->resource->getConnection()->fetchPairs(
                $dataMapper->buildSelectQuery((int)$store->getId(), $ids)
            );

            foreach ($values as $id => $value) {
                $filterValuesPerProduct[$id][$code] = $code == ExtraFilterConfigProvider::RATING_FILTER
                    ? (float)$value
                    : (int)$value;
            }
        }

        foreach ($ids as $id) { // stock filter
            $stockStatus = $this->stockState->getStockStatus($id, $store->getId())->getStockStatus() ? 2 : 1;
            $filterValuesPerProduct[$id]['stock'] = $stockStatus;
        }

        $rootCategory   = $this->categoryRepository->get($store->getRootCategoryId(), $store->getId());
        $indexTableName = $this->getIndexTableName($store->getCode());

        // update products metadata
        foreach ($rows as $sku => $row) {
            if (!isset($filterValuesPerProduct[$row['id']])) {
                continue;
            }

            $feedData = $row['feed_data'];

            foreach ($filterValuesPerProduct[$row['id']] as $code => $value) {
                $isRatingFilter = $code == 'rating';

                $feedData['attributes'][] = [
                    "attributeCode" => $this->getFilterParamName($code),
                    "type"          => $isRatingFilter ? "select" : "boolean",
                    "value"         => [$value],
                    "valueId"       => $isRatingFilter ? [$value] : [$value ? 1 : 0]
                ];
            }

            $rootCategoryIncluded = false;
            $categoryData         = isset($feedData['categoryData']) ? $feedData['categoryData'] : [];

            foreach ($categoryData as $catData) {
                if ($catData['categoryId'] == $rootCategory->getId()) {
                    $rootCategoryIncluded = true;
                    break;
                }
            }

            if (!$rootCategoryIncluded) {
                $positionQuery = "SELECT position FROM " . $indexTableName  . " WHERE category_id = " . $rootCategory->getId()
                    . " AND product_id = " . $row['id'] . " AND store_id = " . $store->getId();

                $pos = $this->resource->getConnection()->query($positionQuery)->fetchAll();
                $pos = count($pos) ? $pos[0]['position'] : 0;

                $feedData['categoryData'][] = [
                    'categoryId'      => $rootCategory->getId(),
                    'categoryPath'    => $rootCategory->getUrlKey(),
                    'productPosition' => $pos
                ];
            }

            $row['feed_data'] = SerializeService::encode($feedData);

            $this->resource->getConnection()->insertOnDuplicate(
                $this->resource->getTableName($metadata->getFeedTableName()),
                $row,
                $keys
            );
        }
    }

    private function getIndexTableName(string $storeViewCode): string
    {
        $connection = $this->resource->getConnection();
        $storeId    = $connection->fetchOne(
            $connection->select()
                ->from(['store' => $this->resource->getTableName('store')],'store_id')
                ->where('store.code = ?', $storeViewCode)
        );
        $catalogCategoryProductDimension = new Dimension(
            \Magento\Store\Model\Store::ENTITY,
            $storeId
        );

        $tableName = $this->tableResolver->resolve(
            AbstractAction::MAIN_INDEX_TABLE,
            [$catalogCategoryProductDimension]
        );

        return $tableName;
    }
}
