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

namespace Magezon\ProductPageBuilder\Block\Product\Element;

class Review extends \Magezon\ProductPageBuilder\Block\Product\Element
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewsColFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context             $context           
     * @param \Magento\Framework\App\Http\Context                          $httpContext       
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface            $priceCurrency     
     * @param \Magento\Framework\Registry                                  $registry          
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory 
     * @param array                                                        $data              
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $priceCurrency, $registry, $data);
        $this->_reviewsColFactory = $collectionFactory;
    }

    /**
     * Get URL for ajax call
     *
     * @return string
     */
    public function getProductReviewUrl()
    {
        return $this->getUrl(
            'review/product/listAjax',
            [
				'_secure' => $this->getRequest()->isSecure(),
				'id'      => $this->getProduct()->getId()
            ]
        );
    }

    /**
     * Get size of reviews collection
     *
     * @return int
     */
    public function getCollectionSize()
    {
        $collection = $this->_reviewsColFactory->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addEntityFilter(
            'product',
            $this->getProduct()->getId()
        );

        return $collection->getSize();
    }
}