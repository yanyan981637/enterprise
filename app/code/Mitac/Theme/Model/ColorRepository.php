<?php
namespace Mitac\Theme\Model;

use Magento\Store\Model\Store;
use Mitac\Theme\Api\ColorRepositoryInterface;
use Mitac\Theme\Api\Data\ColorInterface;
use Mitac\Theme\Model\ResourceModel\Color as ResourceColor;
use Magento\Framework\Exception\NoSuchEntityException;

class ColorRepository implements ColorRepositoryInterface
{
    private $resource;
    private $colorFactory;
    private $colorCollectionFactory;

    private $storeManager;

    public function __construct(
        ResourceColor $resource,
        ColorFactory $colorFactory,
        ResourceModel\Color\CollectionFactory $colorCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->colorFactory = $colorFactory;
        $this->colorCollectionFactory = $colorCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function getById($colorId)
    {
        $color = $this->colorFactory->create();
        $this->resource->load($color, $colorId);
        if (!$color->getId()) {
            throw new NoSuchEntityException(__('Color with id "%1" does not exist.', $colorId));
        }
        return $color;
    }

    public function save(ColorInterface $color)
    {
        $this->resource->save($color);
        return $color;
    }

    public function delete(ColorInterface $color)
    {
        $this->resource->delete($color);
        return true;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        // 實現搜索邏輯
        $collection = $this->colorCollectionFactory->create();
        return $collection;
    }

    public function getColorByPage($id, $pageType = ColorInterface::CATEGORY_PAGE, $storeId = null){
        $collection = $this->colorCollectionFactory->create();

        if($storeId === null){
            $storeId = $this->storeManager->getStore()->getId();
        }

        try {
            $collection
                ->addFieldToFilter('enabled', ['eq' => 1])
                ->addFieldToFilter('store_ids', ['finset' => $storeId])
                ->addFieldToFilter($pageType, ['finset' => $id]);

            if($collection->getSize() === 0){
                $collection = $this->colorCollectionFactory->create();
                $collection
                    ->addFieldToFilter('enabled', 1)
                    ->addFieldToFilter('store_ids', ['finset' => Store::DEFAULT_STORE_ID])
                    ->addFieldToFilter($pageType, ['finset' => $id]);
            }

            return $collection->getSize() > 0 ? $collection->getFirstItem() : null;
        }catch (\Exception $e){
            throw $e;
        }

    }
}
