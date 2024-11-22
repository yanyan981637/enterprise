<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 * 
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 * 
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 * 
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 * 
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\ContactForms\Model\Entry;

use Magento\Framework\App\Request\DataPersistorInterface;
use PluginCompany\ContactForms\Model\Entry;
use PluginCompany\ContactForms\Model\ResourceModel\Entry\CollectionFactory;

class AdminEditDataProvider extends DataProvider
{

    protected $loadedData;
    protected $dataPersistor;
    protected $collection;
    private $urlBuilder;

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
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $collectionFactory, $dataPersistor, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->initAdditionalData($model);
            $this->loadedData[$model->getId()] = $model->getData();
        }
        $data = $this->dataPersistor->get('plugincompany_contactforms_entry');
        
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('plugincompany_contactforms_entry');
        }
        
        return $this->loadedData;
    }

    public function initAdditionalData($entry)
    {
        $this
            ->initFormEditUrl($entry)
            ->initFormTitle($entry)
            ->initFileUploads($entry)
        ;
        return $this;
    }

    public function initFormEditUrl($entry)
    {
        $entry->setFormEditUrl(
            $this->urlBuilder->getUrl(
                'plugincompany_contactforms/form/edit',
                ['form_id' => $entry->getFormId()]
            )
        );
        return $this;
    }

    public function initFormTitle($entry)
    {
        if(!$entry->getForm()){
            return $this;
        }
        $entry->setFormTitle(
            $entry->getForm()->getTitle()
        );
        return $this;
    }

    public function initFileUploads(Entry $entry)
    {
        $entry->initUploadedFileUrls();
        return $this;
    }

}
