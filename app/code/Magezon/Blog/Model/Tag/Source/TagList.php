<?php
namespace Magezon\Blog\Model\Tag\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory;

class TagList implements OptionSourceInterface
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
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create();
        $options = [];
        foreach ($collection as $author) {
            $options[] = [
                'label' => $author->getTitle(),
                'value' => $author->getId()
            ];
        }
        return $options;
    }
}
