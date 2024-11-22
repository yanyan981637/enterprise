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

namespace Mirasvit\SearchElastic\InstantProvider;

use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\SearchElastic\SearchAdapter\Manager;

class ConfigProvider
{
    private $manager;

    private $indexNameResolver;

    private $indexRepository;

    public function __construct(
        Manager                 $manager,
        SearchIndexNameResolver $indexNameResolver,
        IndexRepository         $indexRepository
    ) {
        $this->manager           = $manager;
        $this->indexNameResolver = $indexNameResolver;
        $this->indexRepository   = $indexRepository;
    }

    public function getConfig(int $storeId, ?bool $isMisspellEnabled = false): array
    {
        $config = [
            'connection' => $this->manager->getESConfig(),
        ];

        foreach ($this->indexRepository->getList() as $index) {
            $identifier = $index->getIdentifier();
            if ($identifier == 'catalogsearch_fulltext') {
                $identifier = 'magento_catalog_product';
            }

            $config[$identifier] = $this->indexNameResolver->getIndexName($storeId, $index->getIdentifier());
        }

        if ($isMisspellEnabled) {
            $config['mst_misspell_index'] = $this->indexNameResolver->getIndexName($storeId, 'mst_misspell_index');
        }

        return $config;
    }
}
