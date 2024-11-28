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

namespace Magezon\ProductPageBuilder\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\RequestInterface;

class PreviewAction extends Column
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param ContextInterface                                               $context            
     * @param UiComponentFactory                                             $uiComponentFactory 
     * @param RequestInterface                                               $request            
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager       
     * @param \Magento\Backend\Model\Auth\Session                            $authSession        
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory  
     * @param array                                                          $components         
     * @param array                                                          $data               
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->request            = $request;
        $this->storeManager       = $storeManager;
        $this->authSession        = $authSession;
        $this->collectionFactory  = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $productIds = [];
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $productIds[] = $item['entity_id'];
                }
            }
            $productCollection = $this->collectionFactory->create();
            $productCollection->addAttributeToSelect(['url_key']);
            $productCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id']) && $this->request->getParam('profile_id')) {
                    $product = $productCollection->getItemById($item['entity_id']);
                    $url = $this->storeManager->getStore($product->getStoreId())->getBaseUrl() . $product->getData('url_key') . '.html';
                    $url .= '?profile_id=' . $this->request->getParam('profile_id');
                    $url .= '&key=' . $this->authSession->getSessionId();
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href'   => $url,
                            'label'  => __('Preview'),
                            'target' => '_blank'
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}