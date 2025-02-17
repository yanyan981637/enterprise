<?php
namespace Mitac\Homepage\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BlockSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list.
     *
     * @return BlockInterface[]
     */
    public function getItems();
    
    /**
     * Set list.
     *
     * @param BlockInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}
