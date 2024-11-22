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

namespace Mirasvit\Search\Block;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\Registry;
use Magento\Framework\Url as UrlBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\Search\Service\IndexService;


class Result extends Template
{
    /**
     * @var IndexInterface[]
     */
    private $indexes = [];

    private $indexRepository;

    private $indexService;

    private $searchQueryFactory;

    private $config;

    private $registry;

    private $urlCoder;

    private $layerResolver;

    private $categoryCollectionFactory;

    private $urlBuilder;

    public function __construct(
        Context                   $context,
        IndexRepository           $indexRepository,
        IndexService              $indexService,
        QueryFactory              $queryFactory,
        ConfigProvider            $config,
        Registry                  $registry,
        UrlCoder                  $urlCoder,
        LayerResolver             $layerResolver,
        CategoryCollectionFactory $categoryCollectionFactory,
        UrlBuilder                $urlBuilder
    ) {
        $this->indexRepository           = $indexRepository;
        $this->indexService              = $indexService;
        $this->config                    = $config;
        $this->searchQueryFactory        = $queryFactory;
        $this->registry                  = $registry;
        $this->urlCoder                  = $urlCoder;
        $this->layerResolver             = $layerResolver;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->urlBuilder                = $urlBuilder;

        parent::__construct($context);
    }

    public function getCurrentContent(): string
    {
        $index = $this->getCurrentIndex();

        return $this->getContentBlock($index)->toHtml();
    }

    public function getCurrentIndex(): IndexInterface
    {
        $indexId = $this->getRequest()->getParam('index');

        if ($indexId) {
            foreach ($this->getIndexes() as $index) {
                if ($index->getId() == $indexId) {
                    break;
                }
            }
        } else {
            $index = $this->getFirstMatchedIndex();
        }

        if ($index->getIdentifier() != 'catalogsearch_fulltext') {
            $this->getLayout()->getBlock('catalogsearch.leftnav');
            $this->getLayout()->unsetElement('catalogsearch.leftnav');
            $this->getLayout()->getBlock('catalog.leftnav');
            $this->getLayout()->unsetElement('catalog.leftnav');
        }

        return $index;
    }

    /**
     * List of enabled indexes
     * @return IndexInterface[]
     */
    public function getIndexes(): array
    {
        if (!$this->indexes) {
            $result = [];

            $collection = $this->indexRepository->getCollection()
                ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1)
                ->setOrder(IndexInterface::POSITION, 'asc');

            /** @var IndexInterface $index */
            foreach ($collection as $index) {
                $index = $this->indexRepository->get($index->getId());

                if ($index->getIdentifier() === 'magento_search_query') {
                    continue;
                }

                if ($this->config->isMultiStoreModeEnabled()
                    && $index->getIdentifier() == 'catalogsearch_fulltext'
                ) {
                    foreach ($this->_storeManager->getStores(false, true) as $code => $store) {
                        if (in_array($store->getId(), $this->config->getEnabledMultiStores())) {
                            $clone = clone $index;
                            $clone->setData('store_id', $store->getId());
                            $clone->setData('store_code', $code);
                            if ($this->_storeManager->getStore()->getId() != $store->getId()) {
                                $clone->setData('title', $store->getName());
                            }
                            $result[] = $clone;
                        }
                    }
                } else {
                    $result[] = $index;
                }
            }

            $this->indexes = $result;
        }

        return $this->indexes;
    }

    /**
     * First matched index model
     * @return IndexInterface
     */
    public function getFirstMatchedIndex(): IndexInterface
    {
        foreach ($this->getIndexes() as $index) {
            if (($index->getData('store_id') == false
                || $index->getData('store_id') == $this->getCurrentStore()->getId())
            ) {
                return $index;
            }
        }

        return $this->getIndexes()[0];
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Block for index model
     *
     * @param IndexInterface $index
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \Exception
     */
    public function getContentBlock(IndexInterface $index)
    {
        $instance = $this->indexRepository->getInstance($index);
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->getChildBlock($instance->getType());

        if ($instance->getType() == 'catalogsearch_fulltext') {
            $block = $this->_layout->getBlock('search.result');
        }

        if (!$block) {
            throw new \LogicException((string)__('Child block %1 does not exist', $index->getIdentifier()));
        }

        $block->setIndex($index);

        return $block;
    }

    /**
     * Current index size
     * @return int
     */
    public function getCurrentIndexSize(): int
    {
        return $this->getSearchCollection($this->getCurrentIndex())->getSize();
    }

    /**
     * @param IndexInterface $index
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getSearchCollection(IndexInterface $index)
    {
        return $this->indexService->getSearchCollection($index);
    }

    public function getIndexUrl(IndexInterface $index): string
    {
        $query = [
            'index' => $index->getId(),
            'p'     => null,
        ];

        if ($index->hasData('store_id')
            && $index->getData('store_id') != $this->getCurrentStore()->getId()
        ) {
            $query['q']             = $this->getRequest()->getParam('q');
            $query['___store']      = $this->getRequest()->getParam('q');
            $query['___from_store'] = $this->getCurrentStore()->getCode();

            $uenc = $this->_storeManager->getStore($index->getData('store_id'))->getUrl(
                'catalogsearch/result',
                ['_query' => $query]
            );

            return $this->_storeManager->getStore($index->getData('store_id'))->getUrl('stores/store/switch', [
                '_query' => [
                    '___store'      => $index->getData('store_code'),
                    '___from_store' => $this->getCurrentStore()->getCode(),
                    'uenc'          => $this->urlCoder->encode($uenc),
                ],
            ]);
        }

        $params = [
            '_current' => true,
            '_query'   => $query,
        ];

        if ($index->hasData('store_id')) {
            $params['_scope'] = $index->getData('store_id');
        }

        return $this->getUrl('*/*/*', $params);
    }

    /**
     * @return int
     */
    public function getTabsThreshold(): int
    {
        return $this->config->getTabsThreshold();
    }

    public function isHighlightingEnabled(): bool
    {
        return (bool)$this->config->isHighlightingEnabled();
    }

    public function getAggregationData(): array
    {
        if (!$this->config->isSearchIn()) {
            return [];
        }

        $categoryAggregation = $this->layerResolver->get()->getProductCollection()->getFacetedData('category');

        if (empty($categoryAggregation)) {
            return [];
        }

        unset($categoryAggregation[$this->_storeManager->getStore()->getRootCategoryId()]);

        return $this->processCategoryAggregation($categoryAggregation);
    }

    public function getFullName($category): string
    {
        $store  = $this->_storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        $result = [
            '<i>' . $category->getName() . '</i>',
        ];

        do {
            if (!$category->getParentId()) {
                break;
            }
            $category = $category->setStoreId($store->getId())->load($category->getParentId());

            if (!$category->getIsActive() && $category->getId() != $rootId) {
                break;
            }

            if ($category->getId() != $rootId) {
                $result[] = '<i>' . $category->getName() . '</i>';
            }
        } while ($category->getId() != $rootId);

        $result = array_reverse($result);

        return implode('', $result);
    }

    protected function _afterToHtml($html): string
    {
        $numResults = 0;

        foreach ($this->getIndexes() as $index) {
            $numResults += $this->getSearchCollection($index)->getSize();
        }

        $this->registry->register('QueryTotalCount', $numResults, true);

        $this->searchQueryFactory->get()
            ->saveNumResults($numResults);

        return $html;
    }

    private function processCategoryAggregation(array $categoryAggregation): array
    {
        $result     = [];
        $searchTerm = $this->searchQueryFactory->get()->getQueryText();

        $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addPathFilter('1/' . $rootCategoryId)
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->addFieldToFilter('entity_id', ['in' => array_keys($categoryAggregation)])
            ->addFieldToFilter('is_anchor', ['eq' => 1])
            ->setPageSize(10);

        $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id,' . join(',', array_keys($categoryAggregation)) . ')'));

        foreach ($collection as $category) {
            $categoryClone = clone $category;

            $url = $category->getUrlInstance()
                ->addQueryParams(['q' => $searchTerm])
                ->getRebuiltUrl($category->getUrl());

            if (!isset($result[$category->getName()])) {
                $result[$category->getName()] = [
                    'id'        => $category->getId(),
                    'name'      => $category->getName(),
                    'full_name' => $this->getFullName($categoryClone),
                    'url'       => $url,
                    'count'     => $categoryAggregation[$category->getId()]['count'],
                ];
            } else {
                $result[$category->getName()]['name'] = $result[$category->getName()]['full_name'];

                $key = $category->getName() . '_' . $category->getId();

                $result[$key] = [
                    'id'        => $category->getId(),
                    'full_name' => $this->getFullName($categoryClone),
                    'name'      => $this->getFullName($categoryClone),
                    'url'       => $url,
                    'count'     => $categoryAggregation[$category->getId()]['count'],
                ];

                $result[$key]['name'] = $result[$key]['full_name'];
            }
        }

        return $result;
    }
}
