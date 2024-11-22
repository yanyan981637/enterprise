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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Model;

use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Model\ResourceModel\Post\Collection;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class PostManager
{
    /**
     * @var array
     */
    protected $_cache = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     */
	public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CollectionFactory $collectionFactory
	) {
        $this->storeManager      = $storeManager;
        $this->customerSession   = $customerSession;
        $this->collectionFactory = $collectionFactory;
	}

    /**
     * @param  int $storeId 
     * @return Collection
     */
    public function getPostCollection($storeId = null)
    {
		$store = $this->storeManager->getStore($storeId);
		$groupId = $this->customerSession->getCustomerGroupId();
		$collection = $this->collectionFactory->create();
        $collection->addIsActiveFilter()
        ->addStoreFilter($store)
        ->addCustomerGroupFilter($groupId);
        return $collection;
    }

    /**
     * @param  string $year  
     * @return Collection
     */
    public function getPostCollectionByYear($year)
    {
        if (!isset($this->_cache[$year])) {
            $startTime = $year . '-01-01 00:00:00';
            $endTime = $year . '-12-31 23:59:59';
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection();
            $collection->addFieldToFilter(
                'publish_date',
                [
                    'from' => $startTime,
                    'to'   => $endTime
                ]
            );
            $collection->setOrder('publish_date', 'DESC');
            $this->_cache[$year] = $collection;
        }
        return $this->_cache[$year];
    }

    /**
     * @param  string $year  
     * @param  string $month 
     * @return Collection
     */
    public function getPostCollectionByMonth($year, $month)
    {
        if (!isset($this->_cache[$year . $month])) {
            $startTime = $year . '-' . $month . '-01 00:00:00';
            $endTime   = $year . '-' . $month . '-'.date('t') . ' 23:59:59';
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection();
            $collection->addFieldToFilter(
                'publish_date',
                [
                    'from' => $startTime,
                    'to'   => $endTime
                ]
            );
            $collection->setOrder('publish_date', 'DESC');
            $this->_cache[$year . $month] = $collection;
        }
        return $this->_cache[$year . $month];
    }
}