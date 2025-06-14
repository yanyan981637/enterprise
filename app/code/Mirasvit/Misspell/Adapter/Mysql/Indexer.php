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

namespace Mirasvit\Misspell\Adapter\Mysql;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Misspell\Adapter\IndexProvider;

class Indexer
{
    private $resource;

    private $connection;

    private $indexProvider;

    public function __construct(
        ResourceConnection $resource,
        IndexProvider $indexProvider
    ) {
        $this->resource         = $resource;
        $this->connection       = $this->resource->getConnection();
        $this->indexProvider    = $indexProvider;
    }

    public function reindex(int $storeId): void
    {
        $indexTable = $this->resource->getTableName('mst_misspell_index');
        $this->connection->truncateTable($indexTable);

        foreach ($this->indexProvider->getPreparedTextData($storeId) as $rows) {
            $this->connection->insertArray($indexTable, ['keyword', 'trigram', 'frequency'], $rows);
        }

        $this->indexProvider->dropSuggestionTable();
    }
}
