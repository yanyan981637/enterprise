<?php
namespace Mitac\Theme\Ui\Options;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
class CmsPage implements OptionSourceInterface{

    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }
    public function toOptionArray(){
        $collection = $this->collectionFactory->create();

        $options = [];
        foreach ($collection as $item) {
            $options[] = ['label' => $item->getTitle(), 'value' => $item->getId()];
        }
        return $options;
    }
}
