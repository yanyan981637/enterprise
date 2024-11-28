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
namespace PluginCompany\ContactForms\Model\Template;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Template\Filter as CmsFilter;
use Magento\Email\Model\Template\Filter as EmailFilter;
use Magento\Email\Model\Template as EmailTemplate;
use Magento\Framework\Registry;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManager;
use PluginCompany\ContactForms\Model\Template\FilterObjects\Customer;
use PluginCompany\ContactForms\Model\Template\FilterObjects\Product;

class Filter
{

    /**
     * @var CmsFilter
     */
    private $filter;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var Product
     */
    private $product;
    /**
     * @var PrivateContentFilter
     */
    private $privateContentFilter;
    /**
     * @var EmailFilter
     */
    private $emailFilter;
    /**
     * @var StoreManager
     */
    private $storeManager;
    /**
     * @var FormHtmlFilter
     */
    private $formHtmlFilter;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Filter constructor.
     * @param CmsFilter $filter
     * @param EmailFilter $emailFilter
     * @param Registry $registry
     * @param Customer $customer
     * @param Product $product
     * @param StoreManagerInterface $storeManager
     * @param EmailTemplate $emailTemplate
     * @param PrivateContentFilter $privateContentFilter
     * @param FormHtmlFilter $formHtmlFilter
     * @param RequestInterface $request
     */
    public function __construct(
        CmsFilter $filter,
        EmailFilter $emailFilter,
        Registry $registry,
        Customer $customer,
        Product $product,
        StoreManagerInterface $storeManager,
        EmailTemplate $emailTemplate,
        PrivateContentFilter $privateContentFilter,
        FormHtmlFilter $formHtmlFilter,
        RequestInterface $request
    ){
        $this->filter = $filter;
        $this->emailFilter = $emailFilter;
        $this->customer = $customer;
        $this->product = $product;
        $this->registry = $registry;

        $this->filter->setTemplateProcessor([$emailTemplate, 'getTemplateContent']);
        $this->filter->setDesignParams($emailTemplate->getDesignParams());

        $this->emailFilter->setTemplateProcessor([$emailTemplate, 'getTemplateContent']);
        $this->emailFilter->setDesignParams($emailTemplate->getDesignParams());

        $this->privateContentFilter = $privateContentFilter;
        $this->storeManager = $storeManager;
        $this->formHtmlFilter = $formHtmlFilter;
        $this->request = $request;
    }

    public function initFrontEndVariables()
    {
        $this
            ->addCustomerVariables()
            ->addProductVariables()
            ->addCategoryVariables()
            ->addStoreVariables()
            ->addRequestVariables()
        ;
        return $this;
    }

    public function addCustomerVariables()
    {
        $this->setVariables(
            [
                'customer' => $this->getCurrentCustomer(),
                'billing_address' => $this->getCurrentBillingAddress(),
                'shipping_address' => $this->getCurrentShippingAddress()
            ]
        );
        return $this;
    }

    public function getCurrentCustomer()
    {
        return $this->customer;
    }

    public function getCurrentBillingAddress()
    {
        return $this
            ->getCurrentCustomer()
            ->getDefaultBillingAddress();
    }

    public function getCurrentShippingAddress()
    {
        return $this
            ->getCurrentCustomer()
            ->getDefaultShippingAddress();
    }

    public function addProductVariables()
    {
        $this->setVariables(
            ['product' => $this->getCurrentProduct()]
        );
        return $this;
    }

    /**
     * @return Product
     */
    public function getCurrentProduct()
    {
        return $this->product;
    }

    public function addCategoryVariables()
    {
        $this->setVariables(
            ['category' => $this->getCurrentCategory()]
        );
        return $this;
    }

    /**
     * @return Category
     */
    public function getCurrentCategory()
    {
        $category = $this->registry->registry('current_category');
        if (!$category || !$category->getId()) {
            $category = new DataObject();
        }

        return $category;
    }

    public function addStoreVariables()
    {
        $this->setVariables(
            ['store' => $this->getCurrentStore()]
        );
        return $this;
    }

    public function addRequestVariables()
    {
        $this->setVariables(
            ['request' => $this->request]
        );
        return $this;
    }

    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }

    public function setVariables($variables)
    {
        $this->filter
            ->setVariables($variables);
        $this->emailFilter
            ->setVariables($variables);
        return $this;
    }

    public function rewriteDefaultCustomerValuesToKo($string)
    {
        return $this->privateContentFilter->rewriteDefaultCustomerValuesToKo($string);
    }

    public function prepareFormHtml($html)
    {
        return $this->formHtmlFilter->prepareFormHtmlForDisplay($html);
    }

    public function filter($string)
    {
        if(method_exists($this->filter, 'setStrictMode')) {
            $oldMode = $this->filter->setStrictMode(false);
        }

        $result = $this->filter
            ->filter(
                strval($string)
            );

        if(isset($oldMode)) {
            $this->filter->setStrictMode($oldMode);
        }

        return $result;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function filterForEmail($string)
    {
        if(method_exists($this->emailFilter, 'setStrictMode')) {
            $oldMode = $this->emailFilter->setStrictMode(false);
        }

        $result = $this->emailFilter
            ->filter(
                strval($string)
            );

        if(isset($oldMode)) {
            $this->emailFilter->setStrictMode($oldMode);
        }

        return $result;
    }

}


