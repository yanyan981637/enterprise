<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Ui\Component\Listing\File;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\Component\Container;
use Magezon\ProductAttachments\Model\ResourceModel\File\Collection;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->collection = $collectionFactory->create();
        $this->collection->addTotalDownloads();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get authorization info.
     *
     * @return AuthorizationInterface|mixed
     * @deprecated 101.0.7
     */
    private function getAuthorizationInstance()
    {
        if ($this->authorization === null) {
            $this->authorization = ObjectManager::getInstance()->get(AuthorizationInterface::class);
        }
        return $this->authorization;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() == 'file_name') {
            $filter->setField('main_table.file_name');
            parent::addFilter($filter);
        } elseif ($filter->getField() == 'category_name') {
            $filter->setField('category.name');
            parent::addFilter($filter);
        } elseif ($filter->getField() == 'fulltext') {
            $filter->setConditionType('like');
            $filter->setField('main_table.file_name');
            $filter->setValue('%' . $filter->getValue() . '%');
            parent::addFilter($filter);
        } elseif ($filter->getField() == 'is_active') {
            $filter->setField('main_table.is_active');
            parent::addFilter($filter);
        } elseif ($filter->getField() == 'store_id') {
            $filter->setField('store.store_id');
            parent::addFilter($filter);
        } elseif (!empty($this->additionalFilterPool[$filter->getField()])) {
            $this->additionalFilterPool[$filter->getField()]->addFilter($this->searchCriteriaBuilderFactory->create(), $filter);
        } else {
            parent::addFilter($filter);
        }
    }
}
