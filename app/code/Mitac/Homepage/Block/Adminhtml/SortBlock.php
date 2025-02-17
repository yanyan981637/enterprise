<?php
namespace Mitac\Homepage\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\ObjectManagerInterface;
use Mitac\Homepage\Model\ResourceModel\Block\Collection;
use Mitac\Homepage\Helper\StoreData;

class SortBlock extends Template
{
    protected $storeManager;
    protected $BlockCollection;
    protected $objectManager;
    private $blockType;
    protected $storehelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectmanager,
        Collection $blockcollection,
        StoreData $storehelper
    )
    {
      $this->baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
      $this->mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
      $this->BlockCollection = $blockcollection;
      $this->objectManager = $objectmanager;
      $this->blockType = 0;
      $this->StoreHelper = $storehelper;
      parent::__construct($context);
    }

    public function getSortItemsHTML($Store_id, $block_type)
    {
      $toHtml = "";
      $collection = [];
      if ($Store_id)
      {
          $collection = $this->StoreHelper->getStoreSortData($Store_id, $block_type);
      }
      
      foreach ($collection as $rows => $values)
      {
        $toHtml .= "<li data-id='".$values['banners_id']."'><img style='float:left;margin-right: 30px' src='".$this->mediaUrl.$values['img']."'></img>".$values['title']."</li>";
      }

      return $toHtml;
    }

    public function getItemsURL()
    {
      return $this->getUrl('homepage/sort/getitems');
    }

    public function getSaveSortURL()
    {
      return $this->getUrl('homepage/sort/savesort');
    }

    public function getBackURL()
    {
      if ($this->getData('blockType') == 'block') {
        $bakcPath = 'index';
      } else {
        $bakcPath = $this->getData('blockType');
      }
      return $this->getUrl('homepage/'.$bakcPath.'/');
    }

}
