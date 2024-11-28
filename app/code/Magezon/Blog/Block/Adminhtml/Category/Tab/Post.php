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

namespace Magezon\Blog\Block\Adminhtml\Category\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;

class Post extends Extended
{
    /**
     * @var \Magezon\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context       
     * @param \Magento\Backend\Helper\Data            $backendHelper 
     * @param \Magezon\Blog\Model\PostFactory         $postFactory   
     * @param \Magento\Framework\Registry             $coreRegistry  
     * @param array                                   $data          
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magezon\Blog\Model\PostFactory $postFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_postFactory  = $postFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blog_category_posts');
        $this->setDefaultSort('post_id');
        $this->setUseAjax(true);
    }

    /**
     * @return \Magezon\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_blog_category');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in post flag
        if ($column->getId() == 'in_category') {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('post_id', ['in' => $postIds]);
            } elseif (!empty($postIds)) {
                $this->getCollection()->addFieldToFilter('post_id', ['nin' => $postIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }

        $collection = $this->_postFactory->create()->getCollection();
        $this->setCollection($collection);

        if ($this->getCategory()->getPostsReadonly()) {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            $this->getCollection()->addFieldToFilter('post_id', ['in' => $postIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if (!$this->getCategory()->getPostsReadonly()) {
        $this->addColumn(
                'in_category',
                [
                    'type'             => 'checkbox',
                    'name'             => 'in_category',
                    'values'           => $this->_getSelectedPosts(),
                    'index'            => 'post_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }

        $this->addColumn(
            'post_id',
            [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'post_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index'  => 'title'
            ]
        );

        $this->addColumn(
            'identifier',
            [
                'header' => __('URL Key'),
                'index'  => 'identifier'
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header'   => __('Status'),
                'index'    => 'is_active',
                'renderer' => 'Magezon\Blog\Block\Adminhtml\Post\Renderer\Status'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'type'      => 'action',
                'edit_only' => true,
                'sortable'  => false,
                'editable'  => false,
                'filter'    => false,
                'style'     => 'width:10px;',
                'renderer'  => 'Magezon\Blog\Block\Adminhtml\Post\Renderer\Action'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header'           => __('Position'),
                'type'             => 'number',
                'index'            => 'position',
                'header_css_class' => 'mgz-hidden',
                'column_css_class' => 'mgz-hidden',
                'validate_class'   => 'admin__control-text validate-number',
                'editable'         => !$this->getCategory()->getPostsReadonly()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('blog/*/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedPosts()
    {
        $posts = $this->getRequest()->getPost('selected_posts');
        if ($posts === null) {
            $posts = $this->getCategory()->getPostsPosition();
            return array_keys($posts);
        }
        return $posts;
    }
}
