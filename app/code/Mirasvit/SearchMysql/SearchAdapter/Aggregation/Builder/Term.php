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
use Magento\Framework\Search\Request\BucketInterface;
use Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProvider;


class Term
{
    private $metricsBuilder;

    public function __construct(
        Metrics $metricsBuilder
    ) {
        $this->metricsBuilder = $metricsBuilder;
    }

    public function build(
        DataProvider $dataProvider,
        array $dimensions,
        BucketInterface $bucket,
        Table $entityIdsTable
    ): array {
        $metrics = $this->metricsBuilder->build($bucket);

        $select = $dataProvider->getDataSet($bucket, $dimensions, $entityIdsTable);
        $select->columns($metrics);
        if ($bucket->getName() == 'category_bucket') {
            $select->group('category_id');
        } else {
            $select->group(BucketInterface::FIELD_VALUE);
        }

        return $dataProvider->execute($select);
    }
}
