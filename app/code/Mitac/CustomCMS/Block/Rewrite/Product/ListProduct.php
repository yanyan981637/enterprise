<?php
namespace Mitac\CustomCMS\Block\Rewrite\Product;

use PhpParser\Node\Expr\Print_;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    public function getAssociatedMaps($name=null)
    {
        try
        {
            $product = $this->getProductByName($name);

            if($this->checkProductAvailable($product))
            {
                $getUpSell = $product->getUpSellProducts();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');

                /*Map*/
                $category = $this->getCurrentCategory();
                $cid = $category->getId();
                $category = $objectManager->create('Magento\Catalog\Model\Category')->load($cid);

                /* Maps subcategory Updates:18; International Maps:19;Subscriptions:73 */
                $subCategories = $category->getChildrenCategories();
                $_allMaps=[];
                $count = 0;

                foreach($subCategories as $value)
                {
                    $data = [];
                    foreach($getUpSell as $maps)
                    {
                        if(in_array($value->getId(),$maps->getCategoryIds()))
                        {
                            $mapsProduct = $productRepository->get($maps->getSku());
                            array_push($data,$mapsProduct);
                            $count++;
                        }
                    }
                    $_allMaps[$value->getId()]=array('name'=>$value->getName(),'tag'=>'tag'.$value->getId(),'data'=>$data);

                }
                return array("count"=>$count,"product"=>$product,"map"=>$_allMaps);
            }
            else{
                return false;
            }
        } 
        catch(\Magento\Framework\Exception\NoSuchEntityException $e)
        {
            return false;
        }
    }

    public function getAssociatedMapsById($Id=null)
    {
        try
        {
            $product = $this->getProductById($Id);

            if($this->checkProductAvailable($product))
            {
                //-----------------------------------------------------------------------------------//
                $getUpSell = $product->getUpSellProducts();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $storeId = $storeManager->getStore()->getId();
                //-----------------------------------------------------------------------------------//
                /*Map*/
                $category = $this->getCurrentCategory();
                $cid = $category->getId();
                $category = $objectManager->create('Magento\Catalog\Model\Category')->load($cid);
                //-----------------------------------------------------------------------------------//
                /* Maps subcategory Updates:18; International Maps:19;Subscriptions:73 */
                $_allMaps=[]; $data = []; $count = 0;

                foreach($getUpSell as $maps)
                {
                    if(in_array($category->getId(),$maps->getCategoryIds()))
                    {
                        $mapsProduct = $this->getProductbyId($maps->getId());

                        if(in_array($storeId,$mapsProduct->getStoreIds()))
                        {
                            if($mapsProduct->getVisibility()!= 1)
                            {
                                array_push($data,$mapsProduct);
                                $count++;
                            }
                        }
                    }
                }

                $_allMaps[$category->getId()] = array('name'=>$category->getName(),'tag'=>'tag'.$category->getId(),'data'=>$data);
                //-----------------------------------------------------------------------------------//
                $subCategories = $category->getChildrenCategories();
                
                foreach($subCategories as $value)
                {
                    $data = [];

                    foreach($getUpSell as $maps)
                    {
                        if(in_array($value->getId(),$maps->getCategoryIds()))
                        {
                            $mapsProduct = $this->getProductbyId($maps->getId());

                            if(in_array($storeId,$mapsProduct->getStoreIds()))
                            {
                                if($mapsProduct->getVisibility()!= 1)
                                {
                                    array_push($data,$mapsProduct);
                                    $count++;
                                }
                            }
                        }
                    }
                    
                    $_allMaps[$value->getId()] = array('name'=>$value->getName(),'tag'=>'tag'.$value->getId(),'data'=>$data);
                }
                //-----------------------------------------------------------------------------------//
                return array("count"=>$count,"product"=>$product,"map"=>$_allMaps);
            }
            else
            {
                return false;
            }
        }
        catch(\Magento\Framework\Exception\NoSuchEntityException $e)
        {
            return false;
        }
    }

    public function getMediaUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl;   
    }

    public function getProductByName($name)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /* Get product id by name*/ 
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();

        /* Get Product */
        $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\Product');
        $result = $productCollectionFactory->getCollection()
        ->addAttributeToSelect(array('*'))
        ->addAttributeToFilter('name', array('eq' => $name))
        ->addStoreFilter($storeId)
        ->getFirstItem();
        
        return $result;
    }

    public function checkProductAvailable($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();

        if(in_array($storeId,$product->getStoreIds()))
        {
            return true;
        }

        return false;
    }

    /* Device = 6 */
    public function getProductCustomRelated($productId,$customRelated = 6)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();
        
        $productModel = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Link');
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');

        $devices = $productModel->getChildrenIds($productId,$customRelated);
        $productName = Array();

        if(!empty($devices[$customRelated]))
        {
            foreach($devices[$customRelated] as $value)
            {
                $productInfo = $productFactory->create()->setStoreId($storeId)->load($value);

                $deviceStatus  = $productInfo->getStatus();
                $devicegetVisibility  = $productInfo->getVisibility();

                $deviceUrl   = '';
                if($deviceStatus==1 and $devicegetVisibility!=1)
                    $deviceUrl   = $productInfo->getProductUrl();

                $deviceName  = $productInfo->getName();
                $StoreIds = $productInfo->getStoreIds();

                if(!empty($StoreIds))
                {
                    if(in_array($storeId,$StoreIds))
                    {
                        $productName[$deviceName] = $deviceUrl;
                    }
                }
            }
        }

        ksort($productName);
        
        return $productName;
    }

    /* Device = 6 */
    public function getProductCustomRelatedDetail($customRelated = 6)
    {
        //-----------------------------------------------------------------------------------//
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $productModule = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Link');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();
        //-----------------------------------------------------------------------------------//
        $category = $this->getCurrentCategory();
        $cid = $category->getId();
        
        $subCategories = $category->getChildrenCategories();
        $condition = [];
        foreach($subCategories as $value)
        {
            array_push($condition,$value->getId());
        }

        array_push($condition,$cid);

        $accessories = $productCollection->create()->addAttributeToSelect(array('*'))->addCategoriesFilter(['in' => [$condition]])->addStoreFilter($storeId);
        //-----------------------------------------------------------------------------------//
        /* Get Condition */ 
        $TempDevice = [];
        
        foreach($accessories as $accessory)
        {
            if($accessory->getStatus()==1)
            {
                if($accessory->getVisibility()!= 1)
                {
                    $devices = $productModule->getChildrenIds($accessory->getId(),$customRelated);

                    if(!empty($devices[$customRelated]))
                    {
                        foreach($devices[$customRelated] as $value)
                        {
                            $TempDevice[$value] =  $value;
                        }
                    }
                }
            }
        }
        //-----------------------------------------------------------------------------------//
        $productDetail = []; $TempproductDetail = [];

        if(!empty($TempDevice))
        {
            
            $TempProductDetail = $productFactory->create()->getCollection()->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => $TempDevice]);
            foreach($TempProductDetail as $ProductDetail)
            {
                if(!empty($ProductDetail->getStoreIds()))
                {
                    if(in_array($storeId,$ProductDetail->getStoreIds()))
                    {
                        //if($device->getStatus()==1)
                        //{
                            //if($device->getVisibility()!= 1)
                            //{
                                $deviceInfo = array('name'=>$ProductDetail->getName(),'id'=>$ProductDetail->getId(),'img'=>$ProductDetail->getImage());
                                array_push($productDetail,$deviceInfo);

                                $TempproductDetail[$ProductDetail->getId()] = $ProductDetail->getId();
                            //}
                        //}
                    }
                }
            }

            foreach($TempDevice as $value)
            {
                if(empty($TempproductDetail[$value]))
                {
                    $device = $productFactory->create()->setStoreId($storeId)->load($value);

                    if(!empty($device->getStoreIds()))
                    {
                        if(in_array($storeId,$device->getStoreIds()))
                        {
                            //if($device->getStatus()==1)
                            //{
                                //if($device->getVisibility()!= 1)
                                //{
                                    $deviceInfo = array('name'=>$device->getName(),'id'=>$device->getId(),'img'=>$device->getImage());
                                    array_push($productDetail,$deviceInfo);
                                //}
                            //}
                        }
                    }
                }
            }
            
        }
        //-----------------------------------------------------------------------------------//
        $uniResult = array_unique($productDetail,SORT_REGULAR);
        array_multisort($uniResult);
        return $uniResult;
    }

    public function getCurrentCategory()
    {
        $category = $this->getLayer()->getCurrentCategory();

        return $category;
    }

    public function getAllMapsProduct()
    {
        $LINK_TYPE_CUSTOMLINKED = 6;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();

        $productCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $productModule = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Link');
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');

        /*Map*/
        $Tempcategory = $this->getCurrentCategory();
        $cid = $Tempcategory->getId();
        $category = $objectManager->create('Magento\Catalog\Model\Category')->load($cid);

        $subCategories = $category->getChildrenCategories();
        $condition = [];
        foreach($subCategories as $value)
        {
            array_push($condition,$value->getId());
        }
        array_push($condition,$cid);

        /* Get Maps Product */
        $storeId = $storeManager->getStore()->getId();
        $allMaps = $productCollection->create()->addAttributeToSelect(array('*'))->addCategoriesFilter(['in' => $condition])->addStoreFilter($storeId);
        
        /* Get All Maps Related to Custom Product */
        $productName = [];
        if(!empty($allMaps))
        {
            foreach($allMaps as $map)
            {
                $devices = $productModule->getChildrenIds($map->getId(),$LINK_TYPE_CUSTOMLINKED);
                foreach($devices[$LINK_TYPE_CUSTOMLINKED] as $value)
                {
                    $deviceName = $productFactory->create()->setStoreId($storeId)->load($value)->getName();
                    array_push($productName,$deviceName);
                }
            }
        }

        /* Searching*/
        // $input = $this->getRequest()->getParam('q');
        // $pattern = '/'.$input.'/i';
        // $result= preg_grep($pattern, $productName); 
        // $Data = [];
        // $uniResult = array_unique($result);

        $Data = [];
        $uniResult = array_unique($productName);

        if(!empty($uniResult))
        {
            foreach ($uniResult as $value)
            {
                $value = array("title" => $value);
                array_push($Data,$value);
            }
        }
        sort($Data);
        // $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        // $resultJson->setData($Data);

        return $Data;
    }

    public function getProductAccessory($pid)
    {
        $LINK_TYPE_CUSTOMLINKED = 6;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $productCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $productModule = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Link');
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');

        /* Get Accessory Product */
        $category = $this->getCurrentCategory();
        $cid = $category->getId();
        $storeId = $storeManager->getStore()->getId();
        $accessories = $productCollection->create()->addAttributeToSelect(array('*'))->addCategoriesFilter(['in' => [$cid]])->addStoreFilter($storeId);

        /* Get Condition */ 
        $condiAccessory = [];
        foreach($accessories as $accessory)
        {
            $devices = $productModule->getChildrenIds($accessory->getId(),$LINK_TYPE_CUSTOMLINKED);
            foreach($devices[$LINK_TYPE_CUSTOMLINKED] as $value)
            {
                $deviceId = $productFactory->create()->load($value)->getId();

                if($deviceId==$pid)
                {
                    $productInfo = $this->getProductbyId($accessory->getId());

                    if(in_array($storeId,$productInfo->getStoreIds()))
                    {
                        if($productInfo->getVisibility()!= 1)
                        {
                            array_push($condiAccessory,$accessory->getId());
                        }
                    }
                }
            }
        }

        /* Query */ 
        $accessories = $productCollection->create()
                                         ->addAttributeToSelect(array('*'))
                                         ->addCategoriesFilter(['in' => [$cid]])
                                         ->addStoreFilter($storeId)
                                         ->addAttributeToFilter('entity_id', array('in' => $condiAccessory));

        return $accessories;
    }

    public function getLegacyAccessory($cid)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        /* Get Accessory Product */
        $storeId = $storeManager->getStore()->getId();

        $lagacyProducts = $productFactory->create()
                                         ->addAttributeToSelect('*')
                                         ->addCategoriesFilter(['in' => [$cid]])
                                         ->addStoreFilter($storeId)
                                         ->addAttributeToSort('name');
        // $accessories = $productCollection->create()->addAttributeToSelect(array('*'))->addCategoriesFilter(['in' => [$cid]])->addStoreFilter($storeId);

        $TempProducts = Array();

        if (!empty($lagacyProducts)) 
        {
            foreach($lagacyProducts as $product)
            {
                $TempProducts[$product->getName()] = Array(
                                                        'ProductId'          => $product->getId(),
                                                        'ProductUrl'         => $product->getProductUrl(),
                                                        'ProductName'        => $product->getName(),
                                                        'ProductDescription' => $product->getDescription(),
                                                        'ProductSmallImage'  => $product->getSmallImage(),
                                                        'ProductStatus'      => $product->getStatus()
                                                    );
            }
        }

        return $TempProducts;
    }

    public function getProductbyId($pid)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();

        return $productRepository->getById($pid, false, $storeId);
    }

    public function getProductCollectionByCategories($ids)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $ids]);
        $collection->setOrder('name','DESC');

        return $collection;
    }

    public function getCategory($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->create('Magento\Catalog\Model\Category')->load($id);

        return $category;
    }

    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $formKey = $objectManager->get('\Magento\Framework\Data\Form\FormKey');

        return $formKey->getFormKey();
    }

    public function getFilterList($devicesPost, $storeCode)
    {
        // 得到当前选择的 tag
        $current_tag = $this->getRequest()->getParam('current_tag');
        if(empty($current_tag)){
            $current_tag = $this->getCurrentCategory()->getId();
        }

         // 得到当前选择的 tag所有的產品
        $allData = $this->getAssociatedMapsById($devicesPost);
        $_product_collection = $allData['map'][$current_tag]['data'];
        $current_filter = $this->getCurrentFilter($storeCode);

        $attrLists = array();

        foreach($_product_collection as $product) {
            
            $attrs = $product->getAttributes();

            foreach( $attrs as $attr) {

                if ($attr->getIsFilterable() != 0 and $attr->getAttributeCode() !== 'main_category') {

                    if(!array_key_exists($attr->getAttributeCode(), $attrLists)) {
                        $attrLists[$attr->getAttributeCode()] = array(
                            'label' => $attr->getStoreLabel() ? $attr->getStoreLabel() : __($attr->getFrontendLabel()),
                            'value' => array(
                            )
                        );
                    }
                    if ($product->getData($attr->getAttributeCode())) {

                        if (count($current_filter) > 0) {

                            foreach($current_filter as $key => $value){
                                if ($key === $attr->getAttributeCode()) {
                                   $current_attr = array();
                                   $current_attr = $current_filter;
                                //    print_r($current_attr);
                                    $newFIlter = $current_filter;
                                    unset($newFIlter[$key]);
                                    $i = 0;

                                    foreach($newFIlter as $nk => $nv){
                                        if (in_array($product->getData($nk), explode(',', $nv)) or in_array($product->getData($key), explode(',', $current_attr[$key]))) {
                                            // continue;
                                                $i++;
                                            }
                                    }
                                    
                                    if ($i === count($newFIlter)){
                                        $attrLists[$attr->getAttributeCode()]['value'][$product->getData($attr->getAttributeCode())] = $attr->getFrontend()->getValue($product);
                                    }

                                }else {
                                    if (in_array($product->getData($key), explode(',', $current_filter[$key]))) {
                                        $attrLists[$attr->getAttributeCode()]['value'][$product->getData($attr->getAttributeCode())] = $attr->getFrontend()->getValue($product);
                                    }
                                }
                            } 
                        } else {
                            $attrLists[$attr->getAttributeCode()]['value'][$product->getData($attr->getAttributeCode())] = $attr->getFrontend()->getValue($product);
                        }
                       
                    }
                    
                }
            }
        }
        $new_attr_lists = array();

        foreach($attrLists as $key => $attr) {
            
            if(count($attr['value']) > 0) {
                asort($attr['value']);
                $new_attr_lists[$key] = $attr;
            }
        }

        
        return $new_attr_lists;
    }
    
    public function renderUrl($storeCode, $label, $value_id)
    {
        $REQUEST_URI = ltrim(str_replace($storeCode,"",$_SERVER['REQUEST_URI']),'/');
        // echo $REQUEST_URI;
        $param_str = explode('?', $REQUEST_URI);
        // 如果有参数
        if(count( $param_str) > 1) {
            $param = explode('&', $param_str[1]);
            $list = array();
            foreach($param as $item) {
                $a = explode('=', $item);
                $list[$a[0]] = $a[1];
            } 
            if(isset($list[$label])){
                if (in_array($value_id,  explode(',', $list[$label]))) {
                    $list[$label] = rtrim(ltrim(str_replace($value_id, '', $list[$label]), ','), ',');
                } else {
                    $list[$label] = rtrim(ltrim(($list[$label].','.$value_id), ','), ',');
                }
            }else {
                $list[$label] = $value_id;
            }
            $new_url_param = '?';
            foreach($list as $key => $value) {
                if ($value) {
                    $new_url_param = $new_url_param .  $key .'='.$value.'&';
                }
            } 
            $url = rtrim($this->getBaseUrl().$param_str[0].rtrim($new_url_param, '&'), '?');
        } else { // 如果没有参数
            $url = $this->getBaseUrl().$param_str[0].'?'.$label.'='.$value_id;
        }
        return $url;
    }

    public function isChecked($storeCode, $label, $value_id){
        $REQUEST_URI = ltrim(str_replace($storeCode,"",$_SERVER['REQUEST_URI']),'/');
        // echo $REQUEST_URI;
        $param_str = explode('?', $REQUEST_URI);
        // 如果有参数
        if(count( $param_str) > 1) {

            $param = explode('&', $param_str[1]);

            $list = array();
            foreach($param as $item) {
                $a = explode('=', $item);
                $list[$a[0]] = $a[1];
            } 
            
            if (isset($list[$label])) {
                if (in_array($value_id,  explode(',', $list[$label]))){
                    $isChecked = true;
                } else {
                    $isChecked = false;
                }
            } else {
                $isChecked = false;
            }
        } else { // 如果没有参数
            $isChecked = false;
        }
        return $isChecked;
    }

    public function getCurrentFilter($storeCode)
    {
        $list = array();

        $REQUEST_URI = ltrim(str_replace($storeCode,"",$_SERVER['REQUEST_URI']),'/');
        // echo $REQUEST_URI;
        $param_str = explode('?', $REQUEST_URI);
        // 如果有参数
        if(count( $param_str) > 1) {

            $param = explode('&', $param_str[1]);

            foreach($param as $item) {
                $a = explode('=', $item);
                if($a[0] !== 'current_tag') {
                    $list[$a[0]] = $a[1];
                }
            }
        }
        return $list;
    }

    public function RemoveFilter($storeCode, $attr_code = null)
    {
        $REQUEST_URI = ltrim(str_replace($storeCode,"",$_SERVER['REQUEST_URI']),'/');
        $url;
        $param_str = explode('?', $REQUEST_URI);
        $url = $this->getBaseUrl().$param_str[0];
        if(count( $param_str) > 1) {
            
            $param = explode('&', $param_str[1]);

            $list = array();
            foreach($param as $item) {
                $a = explode('=', $item);
                $list[$a[0]] = $a[1];
            }
            $new_url_param = '?';
            foreach($list as $key => $value) {
                if ($key !== $attr_code) {
                    $new_url_param = $new_url_param .  $key .'='.$value.'&';
                }
            }
            $url = rtrim($this->getBaseUrl().$param_str[0].rtrim($new_url_param, '&'), '?');
        }
        return $url;
    }
    // ->addAttributeToFilter('name', array('eq' => $name))
    public function getCurrentShowProduct($storeCode, $products) {
        $current_filter = $this->getCurrentFilter($storeCode);
        $new_products = array();
        if (count($current_filter) > 0) {
            $sort_attr = array();
            foreach($products as $product) {
                $i = 0;
                foreach($current_filter as $key => $value){
                    if (in_array($product->getData($key), explode(',', $value))) {
                        // continue;
                        $i++;
                    }
                }
                if ($i === count($current_filter)){
                    // echo($product->getName());
                    array_push($sort_attr, $product->getName());
                    array_push($new_products, $product);
                }
            }
            
            // print_r($sort_attr);
            array_multisort($sort_attr, SORT_ASC, $new_products);
        }else{
            $new_products = $products;
        }

        return $new_products;
    }

}