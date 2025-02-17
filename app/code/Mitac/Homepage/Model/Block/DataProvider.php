<?php
namespace Mitac\Homepage\Model\Block;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

use Mitac\Homepage\Model\ResourceModel\Block\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $loadedData;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) 
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->pool         = $pool;
        $this->meta         = $this->prepareMeta($this->meta);
    }

    public function prepareMeta(array $meta)
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }

    public function getData()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) 
        {
            $this->data = $modifier->modifyData($this->data);
        }
        //echo '<pre>', print_r($this->data, true), '</pre>'; 

        if(!empty($this->data))
        {
            foreach($this->data as $key=>$value)
            {
                if($key!='config')
                {
                    if(!empty($this->data[$key]['PageIdentifier']) && !empty($this->data[$key]['cms_page_id']))
                    {
                        $this->data[$key]['PageIdentifier'] = $this->data[$key]['cms_page_id'].'<=>'.$this->data[$key]['PageIdentifier'];
                    }
                }
            }
        }
        //echo '<pre>', print_r($this->data, true), '</pre>'; 
        return $this->data;
    }
}
