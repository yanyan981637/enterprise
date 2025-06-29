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

namespace Mirasvit\Misspell\Model;

use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Store\Model\StoreManagerInterface;

class Indexer implements IndexerActionInterface, MviewActionInterface
{
    private $configProvider;
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    public function reindex(): void
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->configProvider->getAdapter()->reindex((int) $store->getId());
        }
    } 

    public function executeFull(): void {
        $this->reindex();
    }

    public function executeList(array $ids): void {}

    public function executeRow($id): void {}

    /**
     * {@inheritdoc}
     */
    public function execute($ids): void {}
}
