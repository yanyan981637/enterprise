<?php
namespace PluginCompany\ContactForms\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\Order\Config;

/**
 * Customer section
 */
class Customer implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var View
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Customer\Model\Data\Customer
     */
    private $customerDataObject;
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;
    /**
     * @var InvoiceCollectionFactory
     */
    private $invoiceCollectionFactory;
    /**
     * @var Config
     */
    private $orderConfig;

    private $orders;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param Config $orderConfig
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View $customerViewHelper,
        OrderCollectionFactory $orderCollectionFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        Config $orderConfig
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->orderConfig = $orderConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [];
        }

        $this->customerDataObject = $this->currentCustomer->getCustomer();

        return [
            'customer' => $this->getCustomerData(),
            'billing_address' => $this->getBillingAddressData(),
            'shipping_address' => $this->getShippingAddressData(),
        ];
    }

    private function getCustomerData()
    {
        return [
            'fullname' => $this->customerViewHelper->getCustomerName($this->customerDataObject),
            'firstname' => $this->customerDataObject->getFirstname(),
            'name' => $this->customerDataObject->getFirstname(),
            'websiteId' => $this->customerDataObject->getWebsiteId(),
            'orders' => $this->getOrderIncrementIdArray(),
            'invoices' => $this->getInvoiceIncrementIdArray()
        ] + $this->customerDataObject->__toArray();
    }

    private function getBillingAddressData()
    {
        if(empty($this->customerDataObject->getAddresses())) {
            return [];
        }
        $billingId = $this->customerDataObject->getDefaultBilling();
        if(!$billingId) return [];

        return $this->getAddressDataById($billingId);
    }

    private function getShippingAddressData()
    {
        if(empty($this->customerDataObject->getAddresses())) {
            return [];
        }
        $shippingId = $this->customerDataObject->getDefaultShipping();
        if(!$shippingId) return [];

        return $this->getAddressDataById($shippingId);
    }

    private function getAddressDataById($id)
    {
        foreach($this->customerDataObject->getAddresses() as $address) {
            if($address->getId() == $id) {
                return $this->getFormattedAddressData($address);
            }
        }
        return [];
    }

    private function getFormattedAddressData(\Magento\Customer\Model\Data\Address $address)
    {
        $data = $address->__toArray();
        if(!empty($data['region']) && is_array($data['region'])) {
            $data = $data['region'] + $data;
        }
        if(!empty($data['street'])) {
            $streets = $data['street'];
            foreach($streets as $index => $street) {
                if($index == 0 ) {
                    $index = '';
                }
                $data['street' . $index] = $street;
            }
            $data['street_full'] = implode(PHP_EOL, $streets);
        }
        return $data;
    }

    public function getInvoiceIncrementIdArray()
    {
        $invoices = $this->getInvoiceCollection();
        if(!$invoices) return [];

        return $invoices->getColumnValues('increment_id');
    }

    public function getInvoiceCollection()
    {
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

    public function getOrderIncrementIdArray()
    {
        if(!$this->getOrderCollection()){
            return [];
        }
        return $this->getOrderCollection()
            ->getColumnValues('increment_id');
    }

    public function getOrderCollection()
    {
        if (!$this->orders) {
            $this->orders = $this->orderCollectionFactory
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

    public function getCustomerId()
    {
        return $this->currentCustomer->getCustomerId();
    }


}
