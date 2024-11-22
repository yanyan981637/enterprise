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
namespace PluginCompany\ContactForms\Model\Template\FilterObjects;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Registry;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use PluginCompany\ContactForms\Helper\Data;

class Customer extends DataObject
{
    private $customerSessionFactory;
    private $registry;
    private $customer;
    private $orders;
    private $invoices;
    private $helper;

    /*
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /*
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    private $invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    public function __construct(
        SessionFactory $customerSessionFactory,
        Registry $registry,
        OrderCollectionFactory $orderCollectionFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        Config $orderConfig,
        Data $helper
    ){
        $this->customerSessionFactory = $customerSessionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->orderConfig = $orderConfig;
        $this->registry = $registry;
        $this->helper = $helper;
        return $this;
    }

    public function __call($method, $args)
    {
        if(substr($method, 0, 3) == 'get') {
            $key = $this->_underscore(substr($method, 3));
            if($this->isAttribute($key)){
                return $this->getAttributeText($key);
            }
        }
        return $this->getCustomer()->{$method}($args);
    }

    public function isAttribute($key)
    {
        $resource = $this->getCustomer()->getResource();
        if(!$resource) return false;
        return $resource->getAttribute($key);
    }

    /**
     * Get attribute text by its code
     *
     * @param string $attributeCode Code of the attribute
     * @return string
     */
    public function getAttributeText($attributeCode)
    {
        $data = $this->getCustomer()->getData($attributeCode);
        if(!is_numeric($data) || !$this->isAttrSelect($attributeCode)){
            return $data;
        }
        return $this->getCustomer()->getResource()
            ->getAttribute($attributeCode)
            ->getSource()
            ->getOptionText(
                $data
            );
    }

    public function isAttrSelect($key){
        if($key == 'store_id'){
            return false;
        }
        $input = $this
            ->getResource()
            ->getAttribute($key)
            ->getFrontendInput()
        ;
        return in_array($input,array('select','multiselect'));
    }

    public function getCustomer()
    {
        if(!$this->customer){
            $this->initCustomer();
        }
        return $this->customer;
    }

    public function initCustomer()
    {
        $customer = $this->getCustomerSession()->getCustomer();
        if(!$customer || !$customer->getId()){
            $customer = new DataObject();
        }
        $this->customer = $customer;
        return $this;
    }

    public function getCustomerSession()
    {
        return $this->customerSessionFactory->create();
    }

    public function getFormatHelper()
    {
        return $this->helper;
    }

    public function getInvoiceNumbersAsOptions()
    {
        $idsArray = $this->getAllInvoiceIncrementIds();
        return $this->getFormatHelper()
            ->formatArrayAsFormHtmlOptions($idsArray)
            ;
    }

    public function getAllInvoiceIncrementIds()
    {
        $invoices = $this->getInvoiceCollection();
        if(!$invoices) return [];

        return $invoices->getColumnValues('increment_id');
    }

    public function getInvoiceCollection()
    {
        if(!$this->isCustomerLoggedIn()){
            return false;
        }

        $collection = $this->invoiceCollectionFactory->create();
        $collection
            ->getSelect()
            ->joinLeft(
                ['order' => $collection->getTable('sales_order')],
                'order.entity_id=main_table.order_id',
                array('customer_id' => 'customer_id')
            );
        $collection
            ->addFieldToFilter('customer_id',$this->getCustomerId());
        return $collection;
    }

    public function getOrderNumbersAsOptions()
    {
        $idsArray = $this->getOrderIncrementIdArray();
        return $this->getFormatHelper()->formatArrayAsFormHtmlOptions($idsArray);
    }

    public function getOrderIncrementIdArray()
    {
        if(!$this->getOrderCollection()){
            return [];
        }
        return $this->getOrderCollection()
            ->getColumnValues('increment_id');
    }

    public function isCustomerLoggedIn()
    {
        return (bool)$this->getCustomerId();
    }

    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    public function getOrderCollection()
    {
        if (!$this->isCustomerLoggedIn()) {
            return false;
        }

        if (!$this->orders) {
            $this->orders = $this->getOrderCollectionFactory()
                ->create($this->getCustomerId())
                ->addFieldToSelect(
                    'increment_id'
                )->addFieldToFilter(
                    'status',
                    ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
                );
        }
        return $this->orders;
    }

    /**
     * @return Config
     */
    public function getOrderConfig()
    {
        return $this->orderConfig;
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return OrderCollectionFactory
     */
    public function getOrderCollectionFactory()
    {
        return $this->orderCollectionFactory;
    }

    /**
     * @return InvoiceCollectionFactory
     */
    public function getInvoiceCollectionFactory()
    {
        return $this->invoiceCollectionFactory;
    }

}
