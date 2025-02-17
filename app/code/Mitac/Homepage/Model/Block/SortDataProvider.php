<?php
namespace Mitac\Homepage\Model\Block;

use Magento\Ui\DataProvider\AbstractDataProvider;

use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Model\ResourceModel\Block\Collection;
use Mitac\Homepage\Model\ResourceModel\Block\CollectionFactory;
use Mitac\Homepage\Model\Block;

class SortDataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) 
    {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $stores = $this->collection->getItems();

        /** @var BlockInterface|Block $block */
        foreach ($stores as $block) {
            $this->loadedData[$block->getId()] = $block->getData();
        }

        return $this->loadedData;
    }
}
