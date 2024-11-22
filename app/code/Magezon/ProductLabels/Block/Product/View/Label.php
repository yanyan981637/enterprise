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

namespace Magezon\ProductLabels\Block\Product\View;

class Label extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory
     */
    protected $labelCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magezon\ProductLabels\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magezon\ProductLabels\Model\ResourceModel\Label\Collection
     */
    protected $_collection;

    /**
     * @var array
     */
    protected $_productIds;

    /**
     * @var array
     */
    protected $_labeRelations;

    /**
     * @var array
     */
    protected $_lableIds;

    /**
     * @var \Magezon\ProductLabels\Model\ResourceModel\Label\Collection
     */
    protected $_labels;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected  $date;

    /**
     * @param \Magento\Catalog\Block\Product\Context                             $context                
     * @param \Magento\Framework\App\Http\Context                                $httpContext            
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface                  $priceCurrency          
     * @param \Magento\Framework\Stdlib\ArrayUtils                               $arrayUtils             
     * @param \Magento\Framework\App\ResourceConnection                          $resource               
     * @param \Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory $labelCollectionFactory 
     * @param \Magento\Customer\Model\Session                                    $customerSession        
     * @param array                                                              $data                   
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory $labelCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magezon\ProductLabels\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $arrayUtils);
        $this->httpContext              = $httpContext;
        $this->priceCurrency            = $priceCurrency;
        $this->date                     = $date;
        $this->resource                 = $resource;
        $this->labelCollectionFactory   = $labelCollectionFactory;
        $this->customerSession          = $customerSession;
        $this->dataHelper               = $dataHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [
                \Magento\Catalog\Model\Product::CACHE_TAG
            ]
        ]);
    }

    public function toHtml()
    {
        if (!$this->dataHelper->isEnable() || !count($this->getLabels())) return;
        return parent::toHtml();
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'MAGEZON_PRODUCT_LABELS',
            $this->priceCurrency->getCurrencySymbol(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->getTemplate(),
            $this->getProduct()->getId()
        ];
    }

    /**
     * @return \Magezon\ProductLabels\Model\ResourceModel\Label\Collection
     */
    public function getCollection()
    {
        if ($this->_collection == NULL) {
            $groupId    = $this->customerSession->getCustomerGroupId();
            $date       = $this->date->gmtDate('Y-m-d');
            $_store     = $this->_storeManager->getStore();
            $product    = $this->getProduct();
            $collection = $this->labelCollectionFactory->create();
            $collection->addFieldToFilter('is_active', \Magezon\ProductLabels\Model\Label::STATUS_ENABLED)
            ->addStoreFilter($_store)
            ->addFieldToFilter('main_table.label_id', ['in' => $this->getLabelsIds()])
            ->addCustomerGroupFilter($groupId)
            ->addFieldToFilter('from_date', [
                'or' => [
                    0 => ['lteq' => $date],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ])
            ->addFieldToFilter('to_date', [
                'or' => [
                    0 => ['gt' => $date],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ])
            ->setOrder('priority', 'DESC');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        if ($this->_productIds == NULL) {
            $_store  = $this->_storeManager->getStore();
            $product = $this->getProduct();
            $productIds[] = $product->getId();
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $productTypeInstance = $product->getTypeInstance();
                $productTypeInstance->setStoreFilter($_store->getId(), $product);
                $usedProducts        = $productTypeInstance->getUsedProductCollection($product)
                ->addAttributeToSelect(
                    ['name', 'price',  'special_price', 'special_from_date', 'special_to_date']
                );
                foreach ($usedProducts as $_child) {
                    $productIds[] = $_child->getId();
                }
                $product->setData('productlabels_usedproducts', $usedProducts);
            }

            if ($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                $productTypeInstance = $product->getTypeInstance(true);
                $usedProducts = $productTypeInstance->getAssociatedProductCollection($product);
                foreach ($usedProducts as $_child) {
                    $productIds[] = $_child->getId();
                }
                $product->setData('productlabels_usedproducts', $usedProducts);
            }
            $this->_productIds = $productIds;
        }
        return $this->_productIds;
    }

    /**
     * @return array
     */
    public function getLabeRelations()
    {
        if ($this->_labeRelations == NULL) {
            $_store  = $this->_storeManager->getStore();
            $connection = $this->resource->getConnection();
            $select = $connection->select()->from($this->resource->getTableName('mgz_productlabels_label_product'))
            ->where('product_id IN (' . implode(",", $this->getProductIds()) . ')')
            ->where('store_id = ' . $_store->getId());
            $this->_labeRelations = (array) $connection->fetchAll($select);
        }
        return $this->_labeRelations;
    }

    /**
     * @return array
     */
    public function getLabelsIds()
    {
        if ($this->_lableIds == NULL) {
            $labeRelations = $this->getLabeRelations();
            $ids = [];
            foreach ($labeRelations as $_re) {
                if (!in_array($_re['label_id'], $ids)) {
                    $ids[] = $_re['label_id'];
                }
            }
            $this->_lableIds = $ids;
        }
        return $this->_lableIds;
    }

    /**
     * @return \Magezon\ProductLabels\Model\ResourceModel\Label\Collection
     */
    public function getLabels()
    {
        if ($this->_labels == NULL) {
            $labeRelations   = $this->getLabeRelations();
            $product         = $this->getProduct();
            $labelCollection = $this->getCollection();
            $labels          = [];
            $usedProducts = $product->getData('productlabels_usedproducts');
            if (!empty($usedProducts)) {
                foreach ($usedProducts as $_child) {
                    foreach ($labeRelations as $_re) {
                        if ($_re['product_id'] == $_child->getId()) {
                            foreach ($labelCollection as $_label) {
                                if ($_label->getUseForParent() && $_label->getId() == $_re['label_id']) {
                                    $labels[] = $_label; 
                                }
                            }
                        }
                    }
                }
            }
            foreach ($labeRelations as $_re) {
                if ($_re['product_id'] == $product->getId()) {
                    foreach ($labelCollection as $_label) {
                        if ($_label->getId() == $_re['label_id']) {
                            $labels[] = $_label; 
                        }
                    }
                }
            }
            $newLabels = [];
            foreach ($labels as $k => $_label) {
                if (!isset($newLabels[$_label->getId()])) {
                    $newLabels[$_label->getId()] = $_label;
                } else {
                    unset($labels[$k]);
                }
            }
            $this->_labels = $labels;
        }
        return $this->_labels;
    }
}