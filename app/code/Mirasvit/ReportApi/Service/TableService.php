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
 * @package   mirasvit/module-report-api
 * @version   1.0.58
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Service;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\Service\TableServiceInterface;

class TableService implements TableServiceInterface
{
    private $resource;
    private $cache;
    private $serializer;

    /**
     * @var array
     */
    private $storage = null;

    public function __construct(
        ResourceConnection $resource,
        CacheInterface $cache,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->resource = $resource;
        $this->cache    = $cache;
        $this->serializer    = $serializer;
    }

    /**
     * @return string[]
     */
    public function getTables()
    {
        $cacheKey = __METHOD__;

        $data = $this->getCacheValue($cacheKey);

        if ($data === false) {
            $data = $this->resource->getConnection()->getTables();
            $this->setCacheValue($cacheKey, $data);
        }

        return $data;
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    private function getCacheValue($key)
    {
        if (strpos($key, 'tmp_') !== false) {
            return false; // do not cache temporary tables
        }

        if ($this->storage == null) {
            $data = $this->cache->load(__CLASS__);

            if ($data) {
                $this->storage = $this->serializer->unserialize($data);
            } else {
                $this->storage = [];
            }
        }

        if (isset($this->storage[$key])) {
            return $this->storage[$key];
        }

        return false;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    private function setCacheValue($key, $value)
    {
        $this->storage[$key] = $value;

        $this->cache->save($this->serializer->serialize($this->storage), __CLASS__);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getForeignKeys(TableInterface $table)
    {
        $cacheKey = __METHOD__ . $table->getName();

        $data = $this->getCacheValue($cacheKey);
        if ($data === false) {
            $connection = $this->resource->getConnection($table->getConnectionName());
            $data       = $connection->getForeignKeys($this->getTableName($connection, $table));

            $this->setCacheValue($cacheKey, $data);
        }

        return $data;
    }

    /**
     * Get table name.
     * If table with a prefix does not exist, use without prefix one.
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param TableInterface                                 $table
     * @return string
     */
    private function getTableName(\Magento\Framework\DB\Adapter\AdapterInterface $connection, TableInterface $table)
    {
        $tableName = $this->resource->getTableName($table->getName());
        if (!$connection->isTableExists($tableName)) {
            $tableName = $table->getName();
        }

        return $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function describeTable(TableInterface $table)
    {
        $cacheKey = __METHOD__ . $table->getName();

        $data = $this->getCacheValue($cacheKey);
        if ($data === false) {
            $connection = $this->resource->getConnection($table->getConnectionName());
            $data       = $connection->describeTable($this->getTableName($connection, $table));

            $this->setCacheValue($cacheKey, $data);
        }

        return $data;
    }
}
