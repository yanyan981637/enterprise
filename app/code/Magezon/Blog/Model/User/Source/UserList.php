<?php
namespace Magezon\Blog\Model\User\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class UserList implements OptionSourceInterface
{
    /**
     * @var \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory
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
        $collection->setOrder('firstname', 'ASC');
        $options = [];
        foreach ($collection as $author) {
            $options[] = [
                'label' => $author->getFirstname() . ' ' . $author->getLastname(),
                'value' => $author->getId()
            ];
        }
        return $options;
    }
}
