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

namespace PluginCompany\ContactForms\Model\Form;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use PluginCompany\ContactForms\Model\ResourceModel\Form\CollectionFactory;

class AdminEditDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $loadedData;
    protected $dataPersistor;

    protected $collection;

    private $storeManager;
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $configScopeConfigInterface
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $configScopeConfigInterface,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $configScopeConfigInterface;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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
            /** @var $model \PluginCompany\ContactForms\Model\Form */
            $model->getResource()->afterLoad($model);
            $model->afterLoad();
            $model->setFormPageStoreBaseUrls($this->getFormPageBaseUrls());
            $model->setFormPageUrlSuffix($this->getUrlSuffix());
            $model->setFormPageDefaultEnabled($this->getFormPageDefaultEnabled());
            $this->loadedData[$model->getId()] = $model->getData();
        }
        $data = $this->dataPersistor->get('plugincompany_contactforms_form');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('plugincompany_contactforms_form');
        }

        return $this->loadedData;
    }

    public function getFormPageBaseUrls()
    {
        $urls = [];
        foreach($this->storeManager->getStores() as $store)
        {
            $urls[$store->getId()] = $store->getBaseUrl() . $this->getUrlPrefix();
        }
        return $urls;
    }

    public function getUrlPrefix()
    {
        if($this->getPrefix())
        {
            return $this->getPrefix() . DIRECTORY_SEPARATOR;
        }
        return '';
    }

    private function getPrefix()
    {
        return $this->getStoreConfig('plugincompany_contactforms/form/url_prefix');
    }

    public function getStoreConfig($value)
    {
        return $this->scopeConfig->getValue($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getUrlSuffix()
    {
        return $this->getStoreConfig('plugincompany_contactforms/form/url_suffix');
    }

    public function getFormPageDefaultEnabled()
    {
        return (bool)$this->getStoreConfig('plugincompany_contactforms/form/frontendurl');
    }
}
