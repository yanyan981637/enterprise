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
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magezon\Blog\Model\PostFactory;

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
     * @param Context $context
     * @param Data $backendHelper
     * @param PostFactory $postFactory
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        PostFactory $postFactory,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_postFactory  = $postFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blog_post_posts');
        $this->setDefaultSort('post_id');
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
     * @return $this|Post
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in post flag
        if ($column->getId() == 'in_post') {
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
     * @throws LocalizedException
     */
    protected function _prepareCollection()
    {
        if ($this->getPost()->getId()) {
            $this->setDefaultFilter(['in_post' => 1]);
        }

        $collection = $this->_postFactory->create()->getCollection();
        $this->setCollection($collection);

        if ($this->getPost()->getPostsReadonly()) {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            $this->getCollection()->addFieldToFilter('post_id', ['in' => $postIds]);
        }

        if ($this->getPost()->getId()) {
            $this->getCollection()->addFieldToFilter('post_id', ['nin' => $this->getPost()->getId()]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Post
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        if (!$this->getPost()->getPostsReadonly()) {
        $this->addColumn(
                'in_post',
                [
                    'type'             => 'checkbox',
                    'name'             => 'in_post',
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
            'position',
            [
                'header'         => __('Position'),
                'index'          => 'position',
                'validate_class' => 'admin__control-text validate-number',
                'header_css_class' => 'mgz-hidden',
                'column_css_class' => 'mgz-hidden',
                'editable'       => !$this->getPost()->getPostsReadonly()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('blog/*/relatedPosts', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedPosts()
    {
        $posts = $this->getRequest()->getPost('selected_posts');
        if ($posts === null) {
            $posts = $this->getPost()->getPostsPosition();
            return array_keys($posts);
        }
        return $posts;
    }
}
