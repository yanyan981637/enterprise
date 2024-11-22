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

namespace Mirasvit\SearchGraphQl\Model\Resolver\Magento\Catalog;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mirasvit\Search\Model\Index\Context as IndexContext;

class Product implements ResolverInterface
{
    private $layerResolver;

    private $indexContext;

    private $layerBuilder;

    private $defaultParams = ['sort' => ['relevance' => 'DESC'], 'filter' => []];

    private $size          = 0;

    private $aggregations  = null;

    public function __construct(
        LayerResolver $layerResolver,
        IndexContext  $indexContext,
        LayerBuilder  $layerBuilder
    ) {
        $this->layerResolver = $layerResolver;
        $this->indexContext  = $indexContext;
        $this->layerBuilder  = $layerBuilder;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args)) {
            if ($field->getName() == 'size') {
                return $this->size;
            } elseif ($field->getName() == 'aggregations') {
                return $this->getAggregations($context);
            }
        }

        foreach ($this->defaultParams as $parameter => $defaultValue) {
            if (!isset($args[$parameter])) {
                $args[$parameter] = $defaultValue;
            }
        }

        $layer      = $this->layerResolver->get();
        $collection = $layer->getProductCollection();
        $collection->addAttributeToSelect('*');
        $searcher = $this->indexContext->getSearcher();
        $searcher->setInstance($value['instance']);

        if (str_contains((string)$collection->getSelect(), '`e`')) {
            $searcher->joinMatches($collection, 'e.entity_id', $args);
        } else {
            $searcher->joinMatches($collection, 'main_table.entity_id', $args);
        }

        $collection->setPageSize($args['pageSize'])
            ->setOrder($args['sort']);

        $items = [];
        foreach ($collection as $product) {
            $productData          = $product->getData();
            $productData['model'] = $product;
            $items[]              = $productData;
        }

        $this->size         = $searcher->getTotal();
        $this->aggregations = $searcher->getAggregations();

        return $items;
    }

    private function getSearcher(array $value)
    {
        $layer    = $this->layerResolver->get();
        $searcher = $this->indexContext->getSearcher();
        $searcher->setInstance($value['instance']);

        return $searcher;
    }

    private function getAggregations($context)
    {
        if ($this->aggregations) {
            $store   = $context->getExtensionAttributes()->getStore();
            $storeId = (int)$store->getId();

            return $this->layerBuilder->build($this->aggregations, $storeId);
        } else {
            return [];
        }
    }
}
