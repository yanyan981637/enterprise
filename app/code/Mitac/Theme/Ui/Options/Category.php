<?php

namespace Mitac\Theme\Ui\Options;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
class Category implements OptionSourceInterface
{

    protected $_categoryCollectionFactory;

    protected $_categoryRepository;

    protected $options = [];

    public function __construct(
         CollectionFactory $categoryCollectionFactory,
         CategoryRepositoryInterface $categoryRepository,

    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryRepository = $categoryRepository;
    }
    private function getChildCategory($categoryId)
    {
        try {
            return $this->_categoryRepository->get($categoryId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }


    public function toOptionArray()
    {
        if($this->options){
            return $this->options;
        }
        $collection = $this->_categoryCollectionFactory->create();

        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();


        foreach ($collection as $category) {
            $this->loadOption($category);
        }

        return $this->options;
    }
    private function loadOption(CategoryInterface $category){
        $prefix = implode('',array_fill(0, $category->getLevel() -1 , '---')) . ' ';
        $this->options[] = [
            'label' => $prefix . $category->getName(),
            'value' => $category->getId(),
        ];
        if($category->hasChildren()){
            $children = explode(',', $category->getChildren());
            foreach ($children as $child){
                $this->loadOption($this->_categoryRepository->get($child));
            }
        }
    }
    private function getOption(CategoryInterface $category){
        $option = [
            'label' => $category->getName() . $category->getChildren() . 's: '. $category->getLevel(),
            'value' => $category->getId(),
            'level' => $category->getLevel(),
            'category_ids' => $category->getChildren(),
        ];

        if($category->hasChildren()){
            $option['children'][] = [
                'label' => $category->getName() . $category->getChildren() . 's: '. $category->getLevel(),
                'value' => $category->getId(),
                'level' => $category->getLevel(),
                'category_ids' => $category->getChildren(),
            ];
            $children = explode(',', $category->getChildren());
            foreach ($children as $child){
                if(!isset($option['children'])){
                    $option['children'] = [];
                }
                $option['children'][] = [
                    'label' => $category->getName() . $category->getChildren() . 's: '. $category->getLevel(),
                    'value' => $category->getId(),
                    'level' => $category->getLevel(),
                    'category_ids' => $category->getChildren(),
                ];
            }
        }
        return $option;
    }
}
