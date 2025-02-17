<?php
namespace Mitac\SystemAPI\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Category implements ArrayInterface
{
    protected $_categoryHelper;
    protected $_collectionFactory;

    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
    )
    {
        $this->_categoryHelper = $catalogCategory;
        $this->_collectionFactory = $collectionFactory;
    }

    /*
     * Return categories helper
     */

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }

    /*  
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value)
        {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        //$categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*');

        $categories = $this->_collectionFactory->create();
        $categories->addAttributeToSelect('*');

        $catagoryList = array();
        foreach ($categories as $category){
            if($category->getName() != 'Root Catalog')
            {
                $catagoryList[$category->getId()] = __($category->getName());
            }
        }

        return $catagoryList;
    }

}
