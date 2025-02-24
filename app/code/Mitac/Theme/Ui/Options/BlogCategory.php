<?php
namespace Mitac\Theme\Ui\Options;
use Magento\Framework\Data\OptionSourceInterface;
use Mageplaza\Blog\Model\ResourceModel\Category\Collection;

class BlogCategory implements OptionSourceInterface {

    private $blogCategoryCollection;
    public function __construct(
        Collection $blogCategoryCollection,
    )
    {
        $this->blogCategoryCollection = $blogCategoryCollection;
    }

    public function toOptionArray(){
        $collection = $this->blogCategoryCollection;
        $collection->addAttributeToSelect('name')->load();
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }
}
