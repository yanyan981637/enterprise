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

namespace Magezon\Blog\Block\Post;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Comment;
use Magezon\Blog\Model\ResourceModel\Comment\Collection;
use Magezon\Blog\Model\ResourceModel\Comment\CollectionFactory;

class Comments extends View
{
    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_COMMENTS_COUNT = 10;

    /**
     * Default value comments per page
     */
    const DEFAULT_COMMENTS_PER_PAGE = 0;

    /**
     * Default show page
     */
    const DEFAULT_SHOW_PAGER = true;

    /**
     * Page var name
     */
    const PAGE_VAR_NAME = 'bc';

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @var array
     */
    protected $_comments;

    /**
     * @var Pager
     */
    protected $pager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected $_items;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dataHelper, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve how many comments should be displayed
     *
     * @return int
     */
    public function getCommentsCount()
    {
        if ($this->hasData('posts_count')) {
            return $this->getData('posts_count');
        }

        if (null === $this->getData('posts_count')) {
            $this->setData('posts_count', self::DEFAULT_COMMENTS_COUNT);
        }

        return $this->getData('posts_count');
    }

    /**
     * Retrieve how many comments should be displayed
     *
     * @return int
     */
    public function getCommentsPerPage()
    {
        if (!$this->hasData('posts_per_page')) {
            $this->setData('posts_per_page', self::DEFAULT_COMMENTS_PER_PAGE);
        }
        return $this->getData('posts_per_page');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Retrieve how many comments should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        return $this->showPager() ? $this->getCommentsPerPage() : $this->getCommentsCount();
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function getPageVarName()
    {
        if (!$this->hasData('page_var_name')) {
            $this->setData('page_var_name', self::PAGE_VAR_NAME);
        }
        return $this->getData('page_var_name');
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $size = $this->getCollection()->getSize();
        if ($this->showPager() && $size > $this->getCommentsPerPage() && $this->getPostsPerPage()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    Pager::class
                );
                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getPageVarName())
                    ->setLimit($this->getCommentsPerPage())
                    ->setTotalLimit($this->getCommentsCount())
                    ->setCollection($this->getCollection());
            }
            if ($this->pager instanceof AbstractBlock) {
                return $this->pager->toHtml();
            }
        }
        return '';
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === NULL) {
            $post = $this->getCurrentPost();
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('post_id', $post->getId());
            $collection->addFieldToFilter('status', Comment::STATUS_APPROVED);
            $collection->addFieldToFilter('parent_id', 0);
            $collection->addPostInformation();
            $collection->addCustomerInformation();
            $collection->setPageSize($this->getPageSize())->setCurPage($this->getRequest()->getParam($this->getPageVarName(), 1));
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    public function getComments()
    {
        if ($this->_comments === NULL) {
            $comments = [];
            $ids = [];
            foreach ($this->getCollection() as $_comment) {
                if (!$_comment->getParentId()) {
                    $comments[] = $_comment;
                    $ids[] = $_comment->getId();
                }
            }
            $post = $this->getCurrentPost();
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('post_id', $post->getId());
            $collection->addFieldToFilter('post_id', ['nin' => $ids]);
            $collection->addFieldToFilter('status', Comment::STATUS_APPROVED);
            $collection->addPostInformation();
            $collection->addCustomerInformation();
            $this->_items = $collection->getItems();
            foreach ($comments as &$_comment) {
                $children = $this->prepareList($_comment);
                if ($children) $_comment->setChildren($children);
            }
            $this->_comments = $comments;
        }
        return $this->_comments;
    }

    /**
     * @param  Comment $comment
     * @return array
     */
    private function prepareList($comment)
    {
        $childrens = [];
        foreach ($this->_items as $k => $_comment) {
            if ($_comment->getParentId() == $comment->getId()) {
                $hasChildren = false;
                $children = $_comment;
                foreach ($this->_items as $_comment2) {
                    if ($_comment2->getParentId() == $_comment->getId()) {
                        $hasChildren = true;
                        break;
                    }
                }
                if ($hasChildren && ($_children = $this->prepareList($children))) {
                    $children->setChildren($_children);
                }
                $childrens[] = $children;
            }
        }
        return $childrens;
    }

    /**
     * @return string
     */
    public function getCommentsHtml()
    {
        $html = '';
        $comments = $this->_comments;
        foreach ($comments as $comment) {
            $html .= $this->getCommentHtml($comment);
        }
        return $html;
    }

    /**
     * @param  Comment $comment
     * @return string
     */
    public function getCommentHtml($comment)
    {
        $html = '';
        $html .= '<li id="comment-' . $comment->getId() . '">';
            $html .= '<div class="blog-comment-wrapper">';
                $html .= '<div class="blog-comment-avatar">';
                    $html .= '<img src="' . $comment->getImageUrl() . '" height="65" width="65"/>';
                $html .= '</div>';
                $html .= '<div class="blog-comment-content-wrapper">';
                    $html .= '<div class="blog-comment-author-wrapper">';
                        $html .= '<div class="blog-comment-author"><span>' . $this->escapeHtml($comment->getAuthor()) . '</span></div>';
                        $html .= '<div class="blog-comment-meta"><a href="' . $comment->getUrl() . '">' . $comment->getCreatedAtFormatted() . '</a></div>';
                    $html .= '</div>';
                        $html .= '<div class="blog-comment-content">' . nl2br($comment->getContentShow()) . ($comment->isShowMore() ? '...' : '');
                        $html.= $comment->isShowMore() ? '<span data-content="' . nl2br(str_replace('"', "'", $comment->getContent())) . '" data-id="' . $comment->getId() . '" data-show="1" class="blog-comment-content-show">' . __('Show more') . '</span>' : '';
                        $html.= '</div>';
                    $html .= '<div class="blog-comment-content-reply">';
                        $html .= '<span class="blog-comment-reply-link" data-parent-id="' . $comment->getId() . '">' . __('Reply') . '</span>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
            if ($children = $comment->getChildren()) {
                $html .= '<ul class="blog-comment-children">';
                foreach ($children as $_comment) {
                    $html .= $this->getCommentHtml($_comment);
                }
                $html .= '</ul>';
            }
        $html .= '</li>';
        return $html;
    }
}