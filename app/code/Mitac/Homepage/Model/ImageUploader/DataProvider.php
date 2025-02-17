<?php
namespace Mitac\Homepage\Model\ImageUploader;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

use Mitac\Homepage\Model\ResourceModel\ImageUploader\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor;
    public    $_storeManager;
    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $helloworldCollectionFactory,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) 
    {
        $this->collection = $helloworldCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager=$storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $baseurl =  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var \Magento\Cms\Model\Block $block */
        foreach ($items as $helloworld)
        {
            $temp = $helloworld->getData();

            if($temp['image']):
              $img = [];
              $img[0]['image'] = $temp['image'];
              $img[0]['url'] = $baseurl.'test/'.$temp['image'];
              $temp['logo'] = $img;
            endif;

            $data = $this->dataPersistor->get('helloworld');

            if (!empty($data)) {
                $helloworld = $this->collection->getNewEmptyItem();
                $helloworld>setData($data);
                $this->loadedData[$helloworld->getLabelId()] = $helloworld->getData();
                $this->dataPersistor->clear('helloworld');
            }else {
                if($items):
                if ($helloworld->getData('image') != null) {

                    $t2[$helloworld>getId()] = $temp;

                    return $t2;
                } else {
                   return $this->loadedData;
                }
                endif;
            }
        }

        return $this->loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'Image\Tmp';
        return $mediaUrl;
    }

}
