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

namespace Magezon\Blog\Block\Adminhtml\Category;

class AssignPosts extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Magezon_Core::assign_items.phtml';

    /**
     * @var \Magezon\Blog\Block\Adminhtml\Category\Tab\Post
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->registry    = $registry;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Magezon\Blog\Block\Adminhtml\Category\Tab\Post::class,
                'category.post.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getJson()
    {
        $posts = $this->getCategory()->getPostsPosition();
        if (!empty($posts)) {
            return $this->jsonEncoder->encode($posts);
        }
        return '{}';
    }

    /**
     * Retrieve current category instance
     *
     * @return \Magezon\Blog\Model\Category|null
     */
    public function getCategory()
    {
        return $this->registry->registry('current_blog_category');
    }

    /**
     * @return string
     */
    public function getElementName()
    {
        return 'category_posts';
    }

    /**
     * @return string
     */
    public function getFormPart()
    {
        return 'blog_category_form';
    }

    /**
     * @return string
     */
    public function getAjaxParam()
    {
        return 'selected_posts';
    }
}
