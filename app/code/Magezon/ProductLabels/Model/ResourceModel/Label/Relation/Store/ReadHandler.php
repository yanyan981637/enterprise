<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @author    Hoang PB - hoangpb@magezon.com
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductLabels\Model\ResourceModel\Label\Relation\Store;

use Magezon\ProductLabels\Model\ResourceModel\Label;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var Label
     */
    protected $labelResource;

    /**
     * @param Label $labelResource
     */
    public function __construct(
        Label $labelResource
    ) {
        $this->labelResource = $labelResource;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {   
        if ($entity->getId()) {
            $storeIds = $this->labelResource->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $storeIds);
        }
        return $entity;
    }
}
