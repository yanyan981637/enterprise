<?php

namespace Mitac\Theme\Ui\Options;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
class Products implements OptionSourceInterface
{
    private $productFactory;
    public function __construct(
        CollectionFactory $productFactory
    ){
        $this->productFactory = $productFactory;
    }

    public function toOptionArray(){
        $collection = $this->productFactory->create();
        $collection->addAttributeToSelect('name')->load();

        foreach ($collection as $product) {
            $options[] = ['label' => $product->getName(), 'value' => $product->getId()];
        }

        return $options;
    }
}
