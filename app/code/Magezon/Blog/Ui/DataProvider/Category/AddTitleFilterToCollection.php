<?php
namespace Magezon\Blog\Ui\DataProvider\Category;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddTitleFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['fulltext']) && $condition['fulltext']) {
            /** @var \Magezon\Blog\Model\ResourceModel\Category\Collection $collection  */
            $collection->addFieldToFilter('title', ['like' => '%' . $condition['fulltext'] . '%']);
        }
    }
}
