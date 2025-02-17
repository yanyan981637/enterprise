<?php
namespace Mitac\CustomCMS\Block\Catalog;

class Products extends \Magento\Framework\View\Element\Template
{
	protected $registry;
	protected $storeManager;
	protected $product;
	protected $_groupCollection;
	protected $_filterProvider;
    protected $_blockFactory;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $_groupCollection,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Cms\Model\BlockFactory $blockFactory,
		array $data = []
	) 
	{
		$this->_registry = $registry;
		$this->_filterProvider = $filterProvider;
		$this->_storeManager = $storeManager;
		$this->_groupCollection = $_groupCollection;
		$this->_blockFactory = $blockFactory;
		parent::__construct($context, $data);
	}

	public function getProduct()
	{
		return $this->_registry->registry('current_product');
	}

	public function getMediaUrl()
	{

		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		return $mediaUrl;	
	}
	
	public function convertVideo($video_url)
	{
		if(preg_match("/youtu/i", $video_url)){//Youtube

			return preg_replace(
				"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
				"https://www.youtube.com/embed/$2",
				trim($video_url, " ")
			);

		} else if (preg_match("/youku/i", $video_url)) {//Youku

			preg_match( "/id_(.*)\.html/i" , $video_url , $match );
			return $match[1];
		}
	}

	/*public function convertYoutube($string) 
	{
		return preg_replace(
			"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
			"https://www.youtube.com/embed/$2",
			trim($string, " ")
		);
	}*/

	public function getUpsellProducts()
	{
		$StoreId = $this->_storeManager->getStore()->getId();

		$currentProduct = $this->getProduct();
		$upsellProducts = array();

		foreach($currentProduct->getUpsellProducts() as $_item){
			$VisibilityValue = $_item->getResource()->getAttributeRawValue($_item->getId(),'visibility',$StoreId); // 2 or 4
			$enabled = $_item->getResource()->getAttributeRawValue($_item->getId(),'status',$StoreId);
			$StoreIds = $_item->getStoreIds();
			if (($VisibilityValue == 2 or $VisibilityValue == 4) and $enabled == 1 and in_array($StoreId, $StoreIds)) {
				$upsellProducts[] = $_item;
			}
		}

		return $upsellProducts;
	}

	public function getCrossSellProducts()
	{
		$StoreId = $this->_storeManager->getStore()->getId();
		$currentProduct = $this->getProduct();
		$upsellProducts = array();

		foreach($currentProduct->getCrossSellProducts() as $_item){
			$VisibilityValue = $_item->getResource()->getAttributeRawValue($_item->getId(),'visibility',$StoreId); // 2 or 4
			$enabled = $_item->getResource()->getAttributeRawValue($_item->getId(),'status',$StoreId);
			$StoreIds = $_item->getStoreIds();
			if (($VisibilityValue == 2 or $VisibilityValue == 4) and $enabled == 1 and in_array($StoreId, $StoreIds)) {
				$upsellProducts[] = $_item;
			}
		}
		
		return $upsellProducts;
	}

	public function truncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) 
	{
		if ($considerHtml) 
		{
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) 
			{
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			
			foreach ($lines as $line_matchings) 
			{
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) 
				{
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) 
					{
						// do nothing
						// if tag is a closing tag
					}
					else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) 
					{
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) 
						{
							unset($open_tags[$pos]);
						}
						// if tag is an opening tag
					} 
					else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) 
					{
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) 
				{
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) 
					{
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) 
						{
							if ($entity[1]+1-$entities_length <= $left) 
							{
								$left--;
								$entities_length += strlen($entity[0]);
							} 
							else 
							{
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} 
				else 
				{
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}

				// if the maximum length is reached, get off the loop
				if($total_length>= $length) 
				{
					break;
				}
			}
		} 
		else 
		{
			if (strlen($text) <= $length) 
			{
				return $text;
			} 
			else 
			{
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
	
		// if the words shouldn't be cut in the middle...
		if (!$exact) 
		{
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) 
			{
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		
		// add the defined ending to the text
		$truncate .= $ending;
		if($considerHtml) 
		{
			// close all unclosed html-tags
			foreach ($open_tags as $tag) 
			{
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	public function getStoreCode ()
	{
		return $this->_storeManager->getStore()->getCode();
	}

	public function getHomeUrl ()
	{
		return $this->_storeManager->getStore()->getBaseUrl();
	}

	public function formatStr($str) {
		return str_ireplace("true","<span class='glyphicon glyphicon-ok'></span>",$str);
	}
	// Get attribute group id
	public function getAttributeGroupId($attributeSetId, $attribute_group_name)
    {
         $groupCollection = $this->_groupCollection->create();
         $groupCollection->addFieldToFilter('attribute_set_id',$attributeSetId);
         $groupCollection->addFieldToFilter('attribute_group_name', $attribute_group_name);

         return $groupCollection->getFirstItem();
    }

	//Get all attribute groups
    public function getAttributeGroups($attributeSetId)
    {
         $groupCollection = $this->_groupCollection->create();
         $groupCollection->addFieldToFilter('attribute_set_id',$attributeSetId);
         
         $groupCollection->setOrder('sort_order','ASC');
         return $groupCollection;

    }

	//get attribute by groups
	public function getGroupAttributes($pro,$groupId, $productAttributes){
        $data=[];
        $no =__('No');
        foreach ($productAttributes as $attribute){
          if ($attribute->isInGroup($pro->getAttributeSetId(), $groupId) && $attribute->getIsVisibleOnFront() ){
              if($attribute->getFrontend()->getValue($pro) && $attribute->getFrontend()->getValue($pro)!='' && $attribute->getFrontend()->getValue($pro)!=$no){
                $data[]=$attribute;
              }
          }
        }
	return $data;
	}

	public function content( $blockId)
        {
            $html = '';
            if ($blockId) {
                $storeId = $this->_storeManager->getStore()->getId();
                /** @var \Magento\Cms\Model\Block $block */
                $block = $this->_blockFactory->create();
                $block->setStoreId($storeId)->load($blockId);

                    $html = $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
            }
            return   $html;
        }

		public function getProductVideo()
		{
			$currentProduct = $this->getProduct();

			$video = array();

			for($i=1; $i<=5; $i++) {

				$id = str_pad((string) $i, '3', 0, STR_PAD_LEFT);
				$product_video_tabname = $currentProduct->getData('product_video_tabname'.$id);
				$product_video_title = $currentProduct->getData('product_video_title'.$id);
				$product_video_desc = $currentProduct->getData('product_video_desc'.$id);
				$product_video_url = $currentProduct->getData('product_video_url'.$id);

				if(preg_match("/youtu/i", $product_video_url)){
					$product_video_type = "Youtube";
				} else if (preg_match("/youku/i", $product_video_url)) {
					$product_video_type = "Youku";
				}

				if (!empty($product_video_tabname) && !empty($product_video_url)) {
					$video[] = array(
						'id' => $i,
						'product_video_tabname' 	=> $product_video_tabname,
						'product_video_title' 		=> $product_video_title,
						'product_video_desc' 		=> $product_video_desc,
						'product_video_url' 		=> $this->convertVideo($product_video_url),
						'product_video_type' 		=> $product_video_type
						//'product_video_url' => $this->convertYoutube(trim($product_video_url, ' '))
					);
				}

			}
			return $video;
		}

		public function getAwardsImages()
		{
			$currentProduct = $this->getProduct();
			$awardsImages = array();
			for($i=1; $i<=5; $i++) {
				$img = $currentProduct->getData('award'.$i);
				if (!empty($img) and $img !== 'no_selection') {
					$awardsImages[] = $this->getMediaUrl().'catalog/product'.$img;
				}
			}
			return $awardsImages;
		}

		public function getInTheBox()
		{
			$product = $this->getProduct();
			$in_the_box = array();
			for ($i=0; $i <=10; $i++) { 
				$id = str_pad((string) $i, '3', 0, STR_PAD_LEFT);
				$in_the_box_img = $product->getData('inb_img'.$id);
				$in_the_box_title = $product->getData('inb_title'.$id);
				if(!empty($in_the_box_img) && !empty($in_the_box_title) && $in_the_box_img !== "no_selection"){
					$in_the_box[] = array(
						'title' => $in_the_box_title,
						'img' => $in_the_box_img 
					);
				}
			}
			return $in_the_box;
		}

		public function getGroupsAttribute($product, $productAttributes, $specsName){
			$specsAttr = array();
			foreach($specsName as $spec){
				$attr_id = $this->getAttributeGroupId($product->getAttributeSetId(), $spec);
				$attr_list = $this->getGroupAttributes($product,$attr_id->getAttributeGroupId(),$productAttributes);
				// echo count($attr_list);
				if(count($attr_list) > 0){
					$i =0;
		
					foreach($attr_list as $attr) {
						// print_r($attr->getData());
						if (strtolower($attr->getFrontend()->getValue($product)) !== 'false') {
							$specsAttr [$spec][$i]['label'] = $attr->getStoreLabel() ? $attr->getStoreLabel() : __($attr->getFrontendLabel());
							$specsAttr [$spec][$i]['value'] = $this->formatStr($attr->getFrontend()->getValue($product));
							$i++;
						}
					}
				}
			}
			return $specsAttr;
		}

}