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

namespace Magezon\Blog\Block\Post;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\Url\Helper\Data;
use Magezon\Blog\Model\Post;

class RelatedProducts extends AbstractProduct
{
    /**
     * @var Data
     */
	protected $urlHelper;

    /**
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magezon\Blog\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var Collection
     */
	protected $_collection;

    /**
     * @param Context $context
     * @param Data $urlHelper
     * @param Visibility $catalogProductVisibility
     * @param CollectionFactory $collectionFactory
     * @param \Magezon\Blog\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
    	Context $context,
    	Data $urlHelper,
    	Visibility $catalogProductVisibility,
        CollectionFactory $collectionFactory,
        \Magezon\Blog\Helper\Data $dataHelper,
    	array $data = []
    ) {
    	parent::__construct($context, $data);
        $this->urlHelper                = $urlHelper;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->collectionFactory        = $collectionFactory;
        $this->dataHelper               = $dataHelper;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->dataHelper->getConfig('post_page/related_products/enabled')) return;
        return parent::toHtml();
    }

    /**
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @return Post
     */
    public function getCurrentPost()
    {
    	return $this->_coreRegistry->registry('current_post');
    }

    /**
     * @return Collection
     */
	public function getCollection()
	{
		if ($this->_collection === NULL) {
            $numberOfProducts = (int)$this->dataHelper->getConfig('post_page/related_products/number_of_products');
            $store = $this->_storeManager->getStore();
			$post = $this->getCurrentPost();
			$collection = $this->collectionFactory->create();
			$collection->addAttributeToFilter(
                'visibility', $this->catalogProductVisibility->getVisibleInCatalogIds()
            );
			$collection = $this->_addProductAttributesAndPrices($collection)->addStoreFilter($store);
            $collection->setPageSize($numberOfProducts);
			$collection->getSelect()->joinLeft(
	            ['mbpp' => $collection->getResource()->getTable('mgz_blog_post_product')],
	            'e.entity_id = mbpp.product_id',
	            []
	        )->where('mbpp.post_id = ?', $post->getId())->group('e.entity_id');
	        $this->_collection = $collection;
	    }
	    return $this->_collection;
	}
}