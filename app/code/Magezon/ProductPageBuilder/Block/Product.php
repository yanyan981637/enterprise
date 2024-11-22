<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Block;

class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magezon_ProductPageBuilder::product.phtml';

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

    /**
     * @var \Magezon\Builder\Helper\Data
     */
    protected $builderHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context       
     * @param \Magento\Framework\Registry                      $registry      
     * @param \Magezon\Builder\Helper\Data                     $builderHelper 
     * @param array                                            $data          
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magezon\Builder\Helper\Data $builderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->builderHelper = $builderHelper;
    }

    /**
     * @return null|Magezon\ProductPageBuilder\Model\Profile
     */
    public function getCurrentProfile()
    {
    	return $this->_coreRegistry->registry('productpagebuilder_profile');
    }

    /**
     * @return string
     */
    public function toHtml()
    {
    	if (!$this->getCurrentProfile()) return;
        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getProfileHtml()
    {
        $profile = $this->getCurrentProfile();
        $block = $this->builderHelper->prepareProfileBlock(\Magezon\Builder\Block\Profile::class, $profile->getProfile());
        $block->setData('custom_classes', 'mgz-productpagebuilder' . $profile->getId() . ' ' . $block->getCustomClasses());
        return $block->toHtml();
    }
}