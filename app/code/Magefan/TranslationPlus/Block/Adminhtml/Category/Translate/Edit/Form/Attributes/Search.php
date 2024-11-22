<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */


namespace Magefan\TranslationPlus\Block\Adminhtml\Category\Translate\Edit\Form\Attributes;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\DB\Helper;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Magento\Framework\Registry;

class Search extends \Magento\Backend\Block\Widget
{
    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @param Context $context
     * @param Helper $resourceHelper
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $resourceHelper,
        CollectionFactory $collectionFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->_resourceHelper = $resourceHelper;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get Options for selector
     *
     * @return array
     */
    public function getSelectorOptions()
    {
        $templateId = $this->getCategory()->getAttributeSetId();
        return [
            'source' => $this->getUrl('catalog/category/suggestAttributes'),
            'minLength' => 0,
            'ajaxOptions' => ['data' => ['template_id' => $templateId]],
            'template' => '[data-template-for="category-attribute-search-' . $this->getGroupId() . '"]',
            'data' => $this->getSuggestedAttributes('', $templateId)
        ];
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param string $labelPart
     * @param int $templateId
     * @return \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection
     */
    public function getSuggestedAttributes($labelPart, $templateId = null)
    {
        $escapedLabelPart = $this->_resourceHelper->addLikeEscape(
            $labelPart,
            ['position' => 'any']
        );
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection */
        $collection = $this->_collectionFactory->create()->addFieldToFilter(
            'frontend_label',
            ['like' => $escapedLabelPart]
        );

        $collection->setExcludeSetFilter($templateId ?: $this->getRequest()->getParam('template_id'))->setPageSize(20);

        $result = [];
        foreach ($collection->getItems() as $attribute) {
            /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $result[] = [
                'id' => $attribute->getId(),
                'label' => $attribute->getFrontendLabel(),
                'code' => $attribute->getAttributeCode(),
            ];
        }
        return $result;
    }

    /**
     * Get url for AddAttribute
     *
     * @return string
     */
    public function getAddAttributeUrl()
    {
        return $this->getUrl('catalog/category/addAttributeToTemplate');
    }
}
