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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Block\Adminhtml\Post\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Product extends Extended
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var Visibility
     */
    protected $_visibility;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Status $status
     * @param Visibility $visibility
     * @param ProductFactory $productFactory
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Status $status,
        Visibility $visibility,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_status         = $status;
        $this->_visibility     = $visibility;
        $this->_productFactory = $productFactory;
        $this->_coreRegistry   = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blog_post_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return \Magezon\Blog\Model\Post
     */
    public function getPost()
    {
        return $this->_coreRegistry->registry('current_post');
    }

    /**
     * @param $column
     * @return $this|Product
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in post flag
        if ($column->getId() == 'in_post') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Product
     * @throws LocalizedException
     */
    protected function _prepareCollection()
    {
        if ($this->getPost()->getId()) {
            $this->setDefaultFilter(['in_post' => 1]);
        }

        $collection = $this->_productFactory->create()->getCollection();
        $collection->addAttributeToSelect(['name', 'url_key', 'visibility', 'status']);
        $this->setCollection($collection);

        if ($this->getPost()->getProductsReadonly()) {
            $postIds = $this->_getSelectedProducts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            $this->getCollection()->addFieldToFilter('post_id', ['in' => $postIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Product
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        if (!$this->getPost()->getProductsReadonly()) {
        $this->addColumn(
                'in_post',
                [
                    'type'             => 'checkbox',
                    'name'             => 'in_post',
                    'values'           => $this->_getSelectedProducts(),
                    'index'            => 'entity_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }

        $this->addColumn(
            'entity_id',
            [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Title'),
                'index'  => 'name'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index'  => 'sku'
            ]
        );

        $this->addColumn(
            'url_key',
            [
                'header' => __('URL Key'),
                'index'  => 'url_key'
            ]
        );

         $this->addColumn(
            'status',
            [
                'header'           => __('Status'),
                'index'            => 'status',
                'type'             => 'options',
                'header_css_class' => 'col-status data-grid-actions-cell',
                'source'           => Status::class,
                'options'          => $this->_status->getOptionArray()
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header'           => __('Visibility'),
                'index'            => 'visibility',
                'type'             => 'options',
                'options'          => $this->_visibility->getOptionArray(),
                'header_css_class' => 'col-visibility data-grid-actions-cell',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header'            => __('Position'),
                'type'              => 'number',
                'index'             => 'position',
                'header_css_class'  => 'mgz-hidden',
                'column_css_class'  => 'mgz-hidden',
                'validate_class'    => 'admin__control-text validate-number',
                'editable'          => !$this->getPost()->getProductsReadonly()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('blog/*/relatedProducts', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $products = $this->getPost()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }
}
