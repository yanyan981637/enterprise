<?php
namespace Mitac\Theme\Ui\DataProvider\Color;

use Magento\Framework\App\Request\DataPersistorInterface;
use Mitac\Theme\Model\ResourceModel\Color\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor;
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

//    public function getData()
//    {
//        if (!$this->getCollection()->isLoaded()) {
//            $this->getCollection()->load();
//        }
//        $items = $this->getCollection()->toArray();
//
//        return [
//            'totalRecords' => $this->getCollection()->getSize(),
//            'items'        => $items['items']
//        ];
//    }

}
