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

namespace Mirasvit\Search\Index\Mirasvit\Kb\Article;

use Magento\Framework\App\ObjectManager;
use Mirasvit\Kb\Model\Article;
use Mirasvit\Search\Index\AbstractInstantProvider;

class InstantProvider extends AbstractInstantProvider
{
    public function getItems(int $storeId, int $limit, int $page = 1): array
    {
        $items = [];

        foreach ($this->getCollection($limit) as $model) {
            $items[] = $this->mapItem($model, $storeId);
        }

        return $items;
    }

    /**
     * @param object $model
     */
    private function mapItem($model, int $storeId): array
    {
        return [
            'name' => $model->getName(),
            'url'  => $model->getUrl(),
        ];
    }

    public function getSize(int $storeId): int
    {
        return $this->getCollection(0)->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        if (!class_exists('Mirasvit\Kb\Model\Article')) {
            return [];
        }

        foreach ($documentData as $entityId => $itm) {
            $om = ObjectManager::getInstance();
            $entity = $om->create('Mirasvit\Kb\Model\Article')->load($entityId);
            $map = $this->mapItem($entity, $storeId);
            $documentData[$entityId][self::INSTANT_KEY] = $map;
        }

        return $documentData;
    }
}
