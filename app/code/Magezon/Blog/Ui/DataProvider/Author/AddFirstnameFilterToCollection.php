<?php
namespace Magezon\Blog\Ui\DataProvider\Author;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddFirstnameFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['fulltext']) && $condition['fulltext']) {
            /** @var \Magezon\Blog\Model\ResourceModel\Author\Collection $collection  */
            $collection->addFieldToFilter('first_name', ['like' => '%' . $condition['fulltext'] . '%']);
        }
    }
}
