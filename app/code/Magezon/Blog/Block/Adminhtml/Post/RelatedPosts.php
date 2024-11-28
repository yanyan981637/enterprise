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

namespace Magezon\Blog\Block\Adminhtml\Post;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template;
use Magento\Framework\View\Element\BlockInterface;
use Magezon\Blog\Block\Adminhtml\Post\Tab\Post;

class RelatedPosts extends Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Magezon_Core::assign_items.phtml';

    /**
     * @var Post
     */
    protected $blockGrid;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->registry    = $registry;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * @return BlockInterface|Post|(Post&BlockInterface)
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                Post::class,
                'post.post.grid'
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
        $posts = $this->getPost()->getPostsPosition();
        if (!empty($posts)) {
            return $this->jsonEncoder->encode($posts);
        }
        return '{}';
    }

    /**
     * Retrieve current post instance
     *
     * @return \Magezon\Blog\Model\Post
     */
    public function getPost()
    {
        return $this->registry->registry('current_post');
    }

    /**
     * @return string
     */
    public function getElementName()
    {
        return 'post_posts';
    }

    /**
     * @return string
     */
    public function getFormPart()
    {
        return 'blog_post_form';
    }

    /**
     * @return string
     */
    public function getAjaxParam()
    {
        return 'selected_posts';
    }
}
