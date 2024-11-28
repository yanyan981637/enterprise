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
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Plugin\Helper\Product;

use Magezon\ProductPageBuilder\Model\Profile as ProfileModel;

class View
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     */
    protected $adminSessionInfoCollectionFactory;

    /**
     * @var \Magezon\ProductPageBuilder\Helper\Data
     */
    protected $date;

    /**
     * @var \Magezon\ProductPageBuilder\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magezon\ProductPageBuilder\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Magezon\SizeChart\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @var array
     */
    protected $profile = [];

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionInfoCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magezon\ProductPageBuilder\Helper\Data $dataHelper
     * @param \Magezon\ProductPageBuilder\Model\ProfileFactory $profileFactory
     * @param \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionInfoCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magezon\ProductPageBuilder\Helper\Data $dataHelper,
        \Magezon\ProductPageBuilder\Model\ProfileFactory $profileFactory,
        \Magezon\ProductPageBuilder\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
    ) {
        $this->_coreRegistry                     = $registry;
        $this->request                           = $request;
        $this->storeManager                      = $storeManager;
        $this->adminSessionInfoCollectionFactory = $adminSessionInfoCollectionFactory;
        $this->date                              = $date;
        $this->dataHelper                        = $dataHelper;
        $this->profileFactory                    = $profileFactory;
        $this->profileCollectionFactory          = $profileCollectionFactory;
    }

    /**
     * @param \Magento\Catalog\Helper\Product\View $subject
     * @param $resultPage
     * @param $product
     * @param $params
     * @return void
     */
    public function beforeInitProductLayout(
        \Magento\Catalog\Helper\Product\View $subject,
        $resultPage,
        $product,
        $params
    ) {
        if ($this->dataHelper->isEnable()) {
            if (($profileId = $this->request->getParam('profile_id')) && $this->request->getParam('key')) {
                $sessionCollection = $this->adminSessionInfoCollectionFactory->create();
                $sessionCollection->addFieldToFilter('session_id', $this->request->getParam('key'))
                ->addFieldToFilter('status', 1);
                if ($sessionCollection->count()) {
                    $profile = $this->getProductProfile($profileId, 'profile', false);
                }
            }
            if (!isset($profile)) {
                if ($productProfileId = $product->getData('ppd_profile_id')) {
                    $profile = $this->getProductProfile($productProfileId, 'profile');
                } else {
                    $profile = $this->getProductProfile($this->getCurrentProduct()->getId(), 'product');
                }
            }
            if (!$profile->getId()) {
                $profileId = $this->dataHelper->getConfig('general/default_profile');
                if ($profileId) {
                    $profile = $this->profileFactory->create();
                    $profile->load($profileId);
                }
            }
            if ($profile && $profile->getId()) {
                $this->_coreRegistry->register('productpagebuilder_profile', $profile);
            }
        }
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param $resultPage
     * @param $product
     * @param $params
     * @return mixed
     */
    public function aroundInitProductLayout(
        $subject,
        callable $proceed,
        $resultPage,
        $product,
        $params = null
    ) {
        $result = $proceed($resultPage, $product, $params);
        $profile = $this->_coreRegistry->registry('productpagebuilder_profile');
        if ($profile && $pageLayout = $profile->getPageLayout()) {
            $resultPage->getConfig()->setPageLayout($pageLayout);
        }
        return $result;
    }

    /**
     * Get current product
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @param  $productID
     * @return array
     */
    public function getProductProfile($id, $type, $checkStore = true)
    {
        if (isset($this->profile[$id])) {
            return $this->profile[$id];
        }
        $date = $this->date->gmtDate('Y-m-d');
        $collection = $this->profileCollectionFactory->create()
        ->addFieldToFilter(
            'is_active',
            ProfileModel::STATUS_ENABLED
        )->setOrder(
            'priority',
            'ASC'
        )->addFieldToFilter(
            'from_date',
            [
                'or' => [
                    0 => ['lteq' => $date],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ]
        )->addFieldToFilter(
            'to_date',
            [
                'or' => [
                    0 => ['gt' => $date],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
                ]
        );
        if ($type == 'product') {
            $collection->getSelect()->joinLeft(
                ['mppb' => $collection->getResource()->getTable('mgz_productpagebuilder_profile_product')],
                'main_table.profile_id = mppb.profile_id',
                []
            )->joinLeft(
                ['mpps' => $collection->getResource()->getTable('mgz_productpagebuilder_profile_store')],
                'main_table.profile_id = mpps.profile_id',
                []
            )->where(
                'mppb.product_id = ?',
                $id
            )->where(
                'mpps.store_id = ?',
                $this->storeManager->getStore()->getId()
            )->group(
                'main_table.profile_id'
            );
        }
        if ($type == 'profile') {
            $collection->addFieldToFilter(
                'profile_id',
                $id
            );
            if ($checkStore) {
                $collection->addStoreFilter(
                    $this->storeManager->getStore()
                );
            }
        }
        $this->profile[$id] = $collection->getFirstItem();
        return $this->profile[$id];
    }
}
