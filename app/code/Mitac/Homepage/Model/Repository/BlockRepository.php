<?php
namespace Mitac\Homepage\Model\Repository;

use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Model\AbstractModel;

use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Api\Data\BlockSearchResultsInterface;
use Mitac\Homepage\Api\Data\BlockSearchResultsInterfaceFactory as SearchResultFactory;
use Mitac\Homepage\Api\BlockRepositoryInterface;
use Mitac\Homepage\Model\ResourceModel\Block\Collection;
use Mitac\Homepage\Model\BlockFactory as BlockFactory;
use Mitac\Homepage\Model\ResourceModel\Block\CollectionFactory;
use Mitac\Homepage\Model\ResourceModel\Block as BlockResource;

class BlockRepository implements BlockRepositoryInterface
{
    protected $instances = [];
    private $searchResultFactory;
    private $collectionFactory;
    private $joinProcessor;
    private $collectionProcessor;
    private $blockFactory;
    private $blockResource;

    public function __construct(
        SearchResultFactory $searchResultFactory,
        CollectionFactory $collectionFactory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        BlockFactory $blockFactory,
        BlockResource $blockResource
    ) 
    {
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionFactory = $collectionFactory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->blockFactory = $blockFactory;
        $this->blockResource = $blockResource;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return BlockSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection, BlockInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getItems());

        return $searchResult;
    }

    /**
     * @param BlockInterface $block
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function save(BlockInterface $block)
    {
        /** @var BlockInterface|AbstractModel $block */
        try {
            $this->blockResource->save($block);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the block: %1', $exception->getMessage()));
        }

        return $block;
    }

    /**
     * @param int $id
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getById($id)
    {
        if (!isset($this->_instances[$id]))
        {
            /** @var BlockInterface|AbstractModel $block */
            $block = $this->blockFactory->create();
            $this->blockResource->load($block, $id);
            if (!$block->getId())
            {
                throw new NoSuchEntityException(__('Block does not exist'));
            }
            $this->instances[$id] = $block;
        }

        return $this->instances[$id];
    }

    /**
     * @param BlockInterface $block
     * @return bool
     * @throws LocalizedException
     */
    public function delete(BlockInterface $block)
    {
        /** @var BlockInterface|AbstractModel $block */
        $id = $block->getId();
        try {
            unset($this->instances[$id]);
            $this->blockResource->delete($block);
        } catch (Exception $e) {
            throw new StateException(__('Unable to remove block %1', $id));
        }
        unset($this->instances[$id]);

        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws LocalizedException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
