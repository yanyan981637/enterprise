<?php
namespace Magezon\Blog\Model\Category\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magezon\Blog\Model\ResourceModel\Category\CollectionFactory;

class CategoryList implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get OptionSourceInterface
     *
     * @return array
     */
    public function toOptionArray($addEmptyField = true)
    {
        $options = [];
        $collection = $this->collectionFactory->create();
        $collection->setOrder('title', 'ASC');
        foreach ($collection as $k => $_category) {
            $options[] = [
                'label' => $_category->getTitle(),
                'value' => $_category->getId()
            ];
        }
        return $options;
    }
}
