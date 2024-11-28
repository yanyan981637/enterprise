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

namespace Magezon\Blog\Block\Adminhtml\Author\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magezon\Blog\Model\Author;
use Magezon\Blog\Model\PostFactory;
use Magezon\Blog\Model\ResourceModel\Author\CollectionFactory;

class Post extends Extended
{
    /**
     * @var PostFactory
     */
    protected $_postFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param PostFactory $postFactory
     * @param Registry $coreRegistry
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        PostFactory $postFactory,
        Registry $coreRegistry,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_postFactory      = $postFactory;
        $this->_coreRegistry     = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blog_author_posts');
        $this->setDefaultSort('post_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Author
     */
    public function getAuthor()
    {
        return $this->_coreRegistry->registry('current_author');
    }

    /**
     * @param $column
     * @return $this|Post
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in post flag
        if ($column->getId() == 'in_author') {
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
     * @return Post
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        if ($this->getAuthor()->getId()) {
            $this->setDefaultFilter(['in_author' => 1]);
        }

        $collection = $this->_postFactory->create()->getCollection();
        $this->setCollection($collection);

        if ($this->getAuthor()->getPostsReadonly()) {
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
        if (!$this->getAuthor()->getPostsReadonly()) {
        $this->addColumn(
                'in_author',
                [
                    'type'             => 'checkbox',
                    'name'             => 'in_author',
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
                'type'     => 'options',
                'renderer' => 'Magezon\Blog\Block\Adminhtml\Post\Renderer\Status',
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );

        $collection = $this->collectionFactory->create();
        $options = [];
        foreach ($collection as $author) {
            $options[$author->getId()] = $author->getFullName();
        }
        $this->addColumn(
            'author_id',
            [
                'header'   => __('Author'),
                'index'    => 'author_id',
                'type'     => 'options',
                'options' => $options
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
                'editable'         => !$this->getAuthor()->getPostsReadonly()
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
            $posts = $this->getAuthor()->getPostsPosition();
            return array_keys($posts);
        }
        return $posts;
    }
}
