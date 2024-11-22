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
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\LayeredNavigation\Repository;


use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\LayeredNavigation\Api\Data\GroupInterface;
use Mirasvit\LayeredNavigation\Api\Data\GroupInterfaceFactory;
use Mirasvit\LayeredNavigation\Model\ResourceModel\Group\Collection;
use Mirasvit\LayeredNavigation\Model\ResourceModel\Group\CollectionFactory;

class GroupRepository
{
    private $factory;

    private $collectionFactory;

    private $entityManager;

    public function __construct(
        GroupInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager
    ) {
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager     = $entityManager;
    }

    public function create(): GroupInterface
    {
        return $this->factory->create();
    }

    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return GroupInterface[]
     */
    public function getGroupsListByAttributeCode(string $attributeCode): array
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(GroupInterface::IS_ACTIVE, true)
            ->addFieldToFilter(GroupInterface::ATTRIBUTE_CODE, $attributeCode);

        $collection->getSelect()->order(GroupInterface::POSITION);

        return $collection->getItems();
    }

    public function get(int $id): ?GroupInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function getByCode(string $code): ?GroupInterface
    {
        $group = $this->getCollection()->addFieldToFilter(GroupInterface::CODE, $code)->getFirstItem();

        return $group->getId() ? $group : null;
    }

    public function save(GroupInterface $model): GroupInterface
    {
        return $this->entityManager->save($model);
    }

    public function delete(GroupInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
