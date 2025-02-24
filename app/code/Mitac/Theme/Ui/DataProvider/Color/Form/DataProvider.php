<?php
namespace Mitac\Theme\Ui\DataProvider\Color\Form;

use Magento\Framework\App\Request\DataPersistorInterface;
use Mitac\Theme\Model\ResourceModel\Color\CollectionFactory;
use Magento\Framework\Registry;
use Mitac\Theme\Helper\FileInfo;
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor;
    protected $loadedData;
    protected $registry;
    protected $fileInfo;
    protected $logger;
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Registry $registry,
        FileInfo $fileInfo,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->registry      = $registry;
        $this->fileInfo       = $fileInfo;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }
    public function prepareMeta(array $meta)
    {
        return $meta;
    }
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        /*
         * 得到當前color， 註冊在edit controller
         * */
        $color = $this->registry->registry('current_color');

        if ($color && $color->getColorId()) {
            $this->loadedData[$color->getColorId()] = $color->getData();
        }

        $data = $this->dataPersistor->get('current_color');
        if (!empty($data)) {
            $color = $this->collection->getNewEmptyItem();
            $color->setData($data);
            $this->loadedData[$color->getColorId()] = $color->getData();
            $this->dataPersistor->clear('current_color');
        }
        $originFaviconUrl = $color->getFaviconUrl();

        if($originFaviconUrl) {

            $isExit = $this->fileInfo->isExist($originFaviconUrl);
            if($isExit) {
                $stat = $this->fileInfo->getStat($originFaviconUrl);
                $favicon_url_data = [
                    'name' => $originFaviconUrl,
                    'size' =>  isset($stat) ? $stat['size'] : 0,
                    'type' => $this->fileInfo->getMimeType($originFaviconUrl),
                    'url' => $this->fileInfo->getFileUrl($originFaviconUrl)
                ];
                $this->loadedData[$color->getColorId()]['favicon_url'] = [];
                $this->loadedData[$color->getColorId()]['favicon_url'][] = $favicon_url_data;
            }
        }

        return $this->loadedData;
    }

}
