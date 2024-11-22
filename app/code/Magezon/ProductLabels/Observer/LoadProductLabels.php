<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Observer;

use Magento\Framework\Event\ObserverInterface;

class LoadProductLabels implements ObserverInterface
{
    /**
     * @var \Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory
     */
    protected $labelCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_productTypeConfigurable;

    /**
     * @var \Magezon\ProductLabels\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory $labelCollectionFactory         
     * @param \Magento\Customer\Model\Session                                    $customerSession                
     * @param \Magento\Store\Model\StoreManagerInterface                         $_storeManager                  
     * @param \Magento\Framework\App\ResourceConnection                          $resource                       
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable       $catalogProductTypeConfigurable 
     * @param \Magezon\ProductLabels\Helper\Data                                 $helperData                     
     */
    public function __construct(
        \Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory $labelCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magezon\ProductLabels\Helper\Data $helperData
    ) {
        $this->labelCollectionFactory   = $labelCollectionFactory;
        $this->customerSession          = $customerSession;
        $this->_storeManager            = $_storeManager;
        $this->_resource                = $resource;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->helperData               = $helperData;
    }
    /**
     * Add price index data for catalog product collection
     * only for front end
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helperData->isEnable()) {
            $collection = $observer->getEvent()->getCollection();
            $groupId    = $this->customerSession->getCustomerGroupId();
            $_store     = $this->_storeManager->getStore();
            $items      = $collection->getItems();

            if (count($items)) {
                $productIds = [];
                foreach ($items as $_item) {
                    $productIds[] = $_item->getId();
                    if ($_item->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                        $productTypeInstance = $_item->getTypeInstance();
                        $productTypeInstance->setStoreFilter($_store->getId(), $_item);
                        $usedProducts        = $productTypeInstance->getUsedProductCollection($_item)
                        ->addAttributeToSelect(
                            ['name', 'price',  'special_price', 'special_from_date', 'special_to_date']
                        );
                        foreach ($usedProducts as $_child) {
                            $productIds[] = $_child->getId();
                        }
                        $_item->setData('productlabels_usedproducts', $usedProducts);
                    }
                    if ($_item->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                        $productTypeInstance = $_item->getTypeInstance(true);
                        $usedProducts = $productTypeInstance->getAssociatedProductCollection($_item);
                        foreach ($usedProducts as $_child) {
                            $productIds[] = $_child->getId();
                        }
                        $_item->setData('productlabels_usedproducts', $usedProducts);
                    }
                }
                $connection       = $this->_resource->getConnection();
                $select           = $connection->select()->from($this->_resource->getTableName('mgz_productlabels_label_product'))
                ->where('product_id IN (' . implode(",", $productIds) . ')')
                ->where('store_id = ' . $_store->getId());
                $labelRelations    = (array) $connection->fetchAll($select);

                $labelIds = [];
                foreach ($labelRelations as $_re) {
                    if (!in_array($_re['label_id'], $labelIds)) {
                        $labelIds[] = $_re['label_id'];
                    }
                }
                $labelCollection = $this->labelCollectionFactory->create();
                $labelCollection->addFieldToFilter('is_active', \Magezon\ProductLabels\Model\Label::STATUS_ENABLED)
                ->addStoreFilter($_store)
                ->addFieldToFilter('main_table.label_id', ['in' => $labelIds])
                ->addCustomerGroupFilter($groupId)
                ->setOrder('priority', 'DESC')
                ->setOrder('label_id', 'DESC');

                foreach ($collection as $_item) {
                    $usedProducts = $_item->getData('productlabels_usedproducts');
                    $labels       = [];
                    if (!empty($usedProducts)) {
                        $isBreak = false;
                        foreach ($usedProducts as $_child) {
                            foreach ($labelCollection as $_label) {
                                foreach ($labelRelations as $_re) {
                                    if ($_re['product_id'] == $_child->getId() && $_label->getUseForParent() && $_label->getId() == $_re['label_id']) {
                                        $_data                      = $_label->getData();
                                        $_data['productlist_image'] = $_label->getProductlistImage();
                                        $labels[]                   = $_data;
                                        if ($_label->getHideLowerPriority()) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    foreach ($labelCollection as $_label) {
                        foreach ($labelRelations as $_re) {
                            if ($_re['product_id'] == $_item->getId() && $_label->getId() == $_re['label_id']) {
                                $_data                      = $_label->getData();
                                $_data['productlist_image'] = $_label->getProductlistImage();
                                $labels[]                   = $_data;

                                if ($_label->getHideLowerPriority()) {
                                    break;
                                }
                            }
                        }
                    }

                    $newLabels = [];
                    foreach ($labels as $k => $_label) {
                        if (!isset($newLabels[$_label['label_id']])) {
                            $newLabels[$_label['label_id']] = $_label;
                        }
                        if ($_label['hide_lower_priority']) {
                            break;
                        }
                    }

                    $_item->setData('label_items', $newLabels);
                }
            }
        }
        return $this;
    }
}
