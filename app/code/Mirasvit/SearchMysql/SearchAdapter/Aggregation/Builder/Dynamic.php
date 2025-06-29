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



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation\Builder;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\BucketInterface;
use Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProvider;


class Dynamic
{
    private $algorithmRepository;

    private $entityStorageFactory;

    public function __construct(
        Repository $algorithmRepository,
        EntityStorageFactory $entityStorageFactory
    ) {
        $this->algorithmRepository  = $algorithmRepository;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    public function build(
        DataProvider $dataProvider,
        array $dimensions,
        BucketInterface $bucket,
        Table $documentsTable
    ) {
        /** @var DynamicBucket $bucket */
        $algorithm = $this->algorithmRepository->get($bucket->getMethod(), [
            'dataProvider' => $dataProvider,
        ]);
        $entityStorage = $this->entityStorageFactory->create($documentsTable);
        $data      = $algorithm->getItems($bucket, $dimensions, $entityStorage);


        $aggregations = $dataProvider->getAggregations($entityStorage);
        if (isset($aggregations['max'])) {
            $data['max'] = [
                'value' => 'max',
                $bucket->getField() => floor(floatval($aggregations['max'])),
                'count' => 1,
            ];
        }

        if (isset($aggregations['min'])) {
            $data['min'] = [
                'value' => 'min',
                $bucket->getField() => floor(floatval($aggregations['min'])),
                'count' => 1,
            ];
        }

        $resultData = $this->prepareData($data);

        return $resultData;
    }

    private function prepareData(array $data): array
    {
        $resultData = [];
        foreach ($data as $value) {
            if (isset($value['value'])) {
                $resultData[$value['value']] = $value;
                continue;
            }
            $from = is_numeric($value['from']) ? $value['from'] : 0;
            $to   = is_numeric($value['to']) ? $value['to'] : 0;
            unset($value['from'], $value['to']);

            $rangeName              = "{$from}_{$to}";
            $resultData[$rangeName] = array_merge(['value' => $rangeName], $value);
        }

        return $resultData;
    }
}
