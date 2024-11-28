<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Model;

class Label extends \Magento\Rule\Model\AbstractModel
{
    /**#@+
     * Label's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**#@+
     * Label's Positions
     */
    const POSITION_TOP                = 1;
    const POSITION_BEFOREIMAGE        = 2;
    const POSITION_AFTERIMAGE         = 3;
    const POSITION_IMAGE_TOPLEFT      = 4;
    const POSITION_IMAGE_TOPCENTER    = 5;
    const POSITION_IMAGE_TOPRIGHT     = 6;
    const POSITION_IMAGE_MIDDLELEFT   = 7;
    const POSITION_IMAGE_MIDDLECENTER = 8;
    const POSITION_IMAGE_MIDDLERIGHT  = 9;
    const POSITION_IMAGE_BOTTOMLEFT   = 10;
    const POSITION_IMAGE_BOTTOMCENTER = 11;
    const POSITION_IMAGE_BOTTOMRIGHT  = 12;
    const POSITION_BEFORETITLE        = 13;
    const POSITION_AFTERTITLE         = 14;
    const POSITION_BEFOREPRICE        = 15;
    const POSITION_AFTERPRICE         = 16;
    const POSITION_BEFOREREVIEW       = 17;
    const POSITION_AFTERVIEW          = 18;
    const POSITION_BEFOREADDTOCART    = 19;
    const POSITION_AFTERADDTOCART     = 20;
    const POSITION_BOTTOM             = 21;

    /**
     * Label cache tag
     */
    const CACHE_TAG = 'productlabels_label';

    /**
     * @var string
     */
    protected $_cacheTag = 'productlabels_label';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'productlabels_label';

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $_actionCollectionFactory;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @param \Magento\Framework\Model\Context                             $context                 
     * @param \Magento\Framework\Registry                                  $registry                
     * @param \Magento\Framework\Data\FormFactory                          $formFactory             
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $localeDate              
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory     $combineFactory          
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory     $actionCollectionFactory 
     * @param \Magezon\Core\Helper\Data                                    $coreHelper              
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource                
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection      
     * @param array                                                        $relatedCacheTypes       
     * @param array                                                        $data                    
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magezon\Core\Helper\Data $coreHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {
        $this->coreHelper               = $coreHelper;
        $this->combineFactory          = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
            );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magezon\ProductLabels\Model\ResourceModel\Label');
    }

    /**
     * Prepare page's statuses.
     * 
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    public function getAvailablePositions()
    {
        return [
            self::POSITION_TOP                => __('Top'),
            self::POSITION_BEFOREIMAGE        => __('Before Image'),
            self::POSITION_AFTERIMAGE         => __('After Left'),
            self::POSITION_IMAGE_TOPLEFT      => __('Image Top Left'),
            self::POSITION_IMAGE_TOPCENTER    => __('Image Top Center'),
            self::POSITION_IMAGE_TOPRIGHT     => __('Image Top Right'),
            self::POSITION_IMAGE_MIDDLELEFT   => __('Image Middle Left'),
            self::POSITION_IMAGE_MIDDLECENTER => __('Image Middle Center'),
            self::POSITION_IMAGE_MIDDLERIGHT  => __('Image Middel Right'),
            self::POSITION_IMAGE_BOTTOMLEFT   => __('Image Bottom Left'),
            self::POSITION_IMAGE_BOTTOMCENTER => __('Image Bottom Center'),
            self::POSITION_IMAGE_BOTTOMRIGHT  => __('Image Bottom Right'),
            self::POSITION_BEFORETITLE        => __('Before Title'),
            self::POSITION_AFTERTITLE         => __('After Title'),
            self::POSITION_BEFOREPRICE        => __('Before Price'),
            self::POSITION_AFTERPRICE         => __('After Price'),
            self::POSITION_BEFOREREVIEW       => __('Before Review'),
            self::POSITION_AFTERVIEW          => __('After Review'),
            self::POSITION_BEFOREADDTOCART    => __('Before Add To Cart'),
            self::POSITION_AFTERADDTOCART     => __('After Add To Cart'),
            self::POSITION_BOTTOM             => __('Bottom')
        ];
    }
    
    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    /**
     * Get conditions field set id.
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get actions field set id.
     *
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }
    /**
     * @return string
     */
    public function getProductpageImage()
    {
        $productpageImage = $this->getData('productpage_image');
        if ($productpageImage && (strpos($productpageImage, 'wysiwyg') !== false || strpos($productpageImage, 'http') === false)) {
            $productpageImage = $this->coreHelper->getMediaUrl() . $productpageImage;
        }
        return $productpageImage;
    }

    /**
     * @return string
     */
    public function getProductlistImage()
    {
        $productlistImage = $this->getData('productlist_image');
        if ($productlistImage && (strpos($productlistImage, 'wysiwyg') !== false || strpos($productlistImage, 'http') === false)) {
            $productlistImage = $this->coreHelper->getMediaUrl() . $productlistImage;
        }
        return $productlistImage;
    }
}