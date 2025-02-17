<?php
namespace Mitac\Homepage\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;

use Mitac\Community\Model\FileUploader;
use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Model\ResourceModel\Block as BlockModel;

class Block extends AbstractModel implements BlockInterface
{
	const CACHE_TAG = 'mitac_homepage_homepage';
	protected $uploader;
	
	public function __construct(
        Context $context,
        Registry $registry,
        FileUploader $uploader,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) 
	{
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->uploader    = $uploader;
    }

	protected function _construct()
	{
		parent::_construct();
		$this->_init(BlockModel::class);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->getData(self::KEY_ID);
	}

	/**
	 * @return int
	 */
	public function getStoresId()
	{
		return $this->getData(self::KEY_STORES_ID);
	}

	/**
	 * @return int
	 */
	public function getSortId()
	{
		return $this->getData(self::KEY_SORT_ID);
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->getData(self::KEY_TYPE);
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->getData(self::KEY_TITLE);
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->getData(self::KEY_TEXT);
	}

	/**
	 * @return string
	 */
	public function getButton()
	{
		return $this->getData(self::KEY_BUTTON);
	}

	/**
	 * @return string
	 */
	public function getImg()
	{
		return $this->getData(self::KEY_IMG);
	}

	/**
	 * @return string
	 */
	public function getPageIdentifier()
	{
		return $this->getData(self::KEY_PAGE_IDENTIFIER);
	}

	/**
	 * @return string
	 */
	public function getCmsPageId()
	{
		return $this->getData(self::KEY_CMS_PAGE_ID);
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->getData(self::KEY_URL);
	}

	/**
	 * @return string
	 */
	public function getYoutube()
	{
		return $this->getData(self::KEY_YOUTUBE);
	}

	/**
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->getData(self::KEY_CREATED_AT);
	}

	/**
	 * @return string
	 */
	public function getUpdatedAt()
	{
		return $this->getData(self::KEY_UPDATED_AT);
	}

	public function setID($id)
	{
		return $this->setData(self::KEY_ID, $id);
	}

	public function setStoresId($stores)
	{
		return $this->setData(self::KEY_STORES_ID, $stores);
	}

	public function setSortId($sortid)
	{
		return $this->setData(self::KEY_SORT_ID, $sortid);
	}

	public function setType($type)
	{
		return $this->setData(self::KEY_TYPE, $type);
	}

	public function setTitle($title)
	{
		return $this->setData(self::KEY_TITLE, $title);
	}

	public function setText($text)
	{
		return $this->setData(self::KEY_TEXT, $text);
	}

	public function setButton($button)
	{
		return $this->setData(self::KEY_BUTTON, $button);
	}

	public function setPageIdentifier($PageIdentifier)
	{
		return $this->setData(self::KEY_PAGE_IDENTIFIER, $PageIdentifier);
	}

	public function setCmsPageId($cmspageid)
	{
		return $this->setData(self::KEY_CMS_PAGE_ID, $cmspageid);
	}

	public function setUrl($url)
	{
		return $this->setData(self::KEY_URL, $url);
	}

	public function setYoutube($youtube)
	{
		return $this->setData(self::KEY_YOUTUBE, $youtube);
	}

	public function getImageUrl()
    {
        $url = false;
        $image = $this->getImg();

        if ($image) 
		{
            if (is_string($image)) 
			{
                $uploader = $this->uploader;
                $url = $uploader->getBaseUrl().$image;
            } 
			else 
			{
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

}
