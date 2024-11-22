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

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data as PriceData;
use Magento\Framework\Registry;

class Product extends DataObject
{
    /** @var \Magento\Catalog\Model\Product */
    private $product;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Data
     */
    private $catalogHelper;
    /**
     * @var PriceData
     */
    private $priceHelper;

    public function __construct(
        RequestInterface $request,
        ProductRepository $productRepository,
        Registry $registry,
        Data $catalogHelper,
        PriceData $priceHelper
    ){
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->catalogHelper = $catalogHelper;
        $this->priceHelper = $priceHelper;
    }

    public function getData($key = '', $index = null)
    {
        $method = $this->convertKeyToGetMethod($key);
        return $this->{$method}();
    }

    private function convertKeyToGetMethod($key)
    {
        return 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }

    public function __call($method, $args)
    {
        if(substr($method, 0, 3) == 'get') {
            $key = $this->_underscore(substr($method, 3));
            if($this->getProductResource() && $this->isAttribute($key)){
                return $this->getAttributeText($key);
            }
        }
        return $this->getProduct()->{$method}($args);
    }

    public function isAttribute($key)
    {
        return $this->getProductResource()->getAttribute($key);
    }

    public function getProductResource()
    {
        return $this->getProduct()->getResource();
    }

    public function getAttributeText($attributeCode)
    {
        $data = $this->getProduct()->getData($attributeCode);
        if(!$this->isAttrSelect($attributeCode)){
            return $data;
        }
        $text = $this->getProduct()->getAttributeText($attributeCode);
        if(is_array($text)){
            $text = implode(', ', $text);
        }
        return $text;
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

    public function getProduct()
    {
        if(!$this->product){
            $this->initProduct();
        }
        return $this->product;
    }

    public function initProduct()
    {
        $product = $this->retrieveProduct();
        if($product) {
            return $this->setProduct($product);
        }
        return $this->setProduct(new DataObject());
    }

    private function retrieveProduct()
    {
        if($this->getProductFromHelper()) {
            return $this->getProductFromHelper();
        }
        if($this->getProductFromRegistry()) {
            return $this->getProductFromRegistry();
        }
        if($this->getProductIdFromRequest()){
            return $this->getProductBasedOnRequest();
        }
        return false;
    }

    private function getProductFromHelper()
    {
        return $this->catalogHelper->getProduct();
    }

    private function getProductFromRegistry()
    {
        $product = $this->registry->registry('product');
        if ($product && $product->getId()) {
            return $product;
        }
        return false;
    }

    private function getProductBasedOnRequest()
    {
        $productId = $this->getProductIdFromRequest();
        if ($productId) {
            return $this->getProductFromRepository($productId);
        }
        return false;
    }

    private function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    private function getProductIdFromRequest()
    {
        $handles = $this->getRequestHandles();
        foreach($handles as $handle){
            if(stristr($handle, 'catalog_product_view_id_')){
                return str_replace('catalog_product_view_id_', '', $handle);
            }
        }
        return false;
    }

    private function getRequestHandles()
    {
        $handles = $this->request->getQueryValue('handles');
        if(is_string($handles)) {
            $handles = json_decode($handles);
        }
        if(is_array($handles)){
            return $handles;
        }
        return [];
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductFromRepository($productId)
    {
        return $this->productRepository->getById($productId);
    }

    public function getFormattedPrice()
    {
        return $this->priceHelper->currency(
            $this->getProduct()->getFinalPrice(),
            true,
            true
        );
    }

    public function getFormattedPriceWithoutContainer()
    {
        return $this->priceHelper->currency(
            $this->getProduct()->getFinalPrice(),
            true,
            false
        );
    }

    public function getFormattedPriceNoContainerAndSymbol()
    {
        return preg_replace("/[^[:digit:],.]/u", '', $this->getFormattedPriceWithoutContainer());
    }
}
