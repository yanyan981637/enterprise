<?php
namespace Mitac\Homepage\Ui\DataProvider\Image\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mitac\Homepage\Model\ResourceModel\Block\CollectionFactory;
use Mitac\Homepage\Helper\StoreData;

class PageblockData implements ModifierInterface
{
    protected $collection;
    protected $Storehelper;

    public function __construct(
        CollectionFactory $imageCollectionFactory,
        StoreData $storedata
    ) 
    {
        $this->collection = $imageCollectionFactory->create();
        $this->Storehelper = $storedata;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    public function modifyData(array $data)
    {
        $items = $this->collection->getItems();
        foreach ($items as $image) {
            $_data = $image->getData();
            if (isset($_data['img'])) {
                $imageArr = [];
                $imageArr[0]['name'] = $image->getImg();
                $imageArr[0]['url'] = $image->getImageUrl();
                $_data['img'] = $imageArr;
            }
            $storesRows = $this->Storehelper->getStores($_data['banners_id']);
            if (count($storesRows) > 0)
            {
                $_data['stores_id'] = '';
                foreach ($storesRows as $rows => $value)
                {
                    $_data['stores_id'] .= implode(",",$value).',';
                }
                $_data['stores_id'] = substr($_data['stores_id'], 0, -1);
            }
            $IdentifierRows = $this->Storehelper->getPageIdenifier($_data['banners_id']);
            if (count($IdentifierRows) > 0)
            {
                $_data['PageIdentifier'] = '';
                foreach ($IdentifierRows as $rows => $value)
                {
                    $_data['PageIdentifier'] .= implode(",",$value).',';
                }
                $_data['PageIdentifier'] = substr($_data['PageIdentifier'], 0, -1);
            }
            $image->setData($_data);
            $data[$image->getId()] = $_data;
        }
        return $data;
    }
}
