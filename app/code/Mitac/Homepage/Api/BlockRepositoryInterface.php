<?php
namespace Mitac\Homepage\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Api\Data\BlockSearchResultsInterface;

interface BlockRepositoryInterface
{
    public function getList(SearchCriteriaInterface $searchCriteria);
    public function save(BlockInterface $stores);
    public function getById($id);
    public function delete(BlockInterface $block);
    public function deleteById($id);
}
