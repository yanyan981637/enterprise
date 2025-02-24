<?php

namespace Mitac\Theme\Ui\Columns;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Product extends Column
{
    protected $productRepository;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productRepository = $productRepository;
    }

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['product_page'])) {
                    $originalProductPage = $item['product_page'];
                    $item['product_page'] = '';
                    foreach (explode(',', $originalProductPage) as $productId) {
                        try {
                            $product = $this->productRepository->getById($productId);
                            $item['product_page'] .= $product->getName() . '<br />';
                        }catch (\Exception $exception){
                            $item['product_page'] .= "<span style='color: red;'>".$exception->getMessage()."</span><br />";
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
