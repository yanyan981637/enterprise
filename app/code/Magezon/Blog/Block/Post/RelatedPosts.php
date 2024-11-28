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

use Magezon\Blog\Block\ListPost;

class RelatedPosts extends View
{
    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->dataHelper->getConfig('post_page/related_posts/enabled')) return;
        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getPostListHtml()
    {
        $numberOfPosts = (int)$this->dataHelper->getConfig('post_page/related_posts/number_of_posts');
        $post = $this->getCurrentPost();
        $collection = $post->getRelatedPostCollection();
        $collection->setPageSize($numberOfPosts);
        $block = $this->getLayout()->createBlock(ListPost::class);
        $block->setTemplate('Magezon_Blog::post/slider.phtml');
        $block->setCollection($collection);
        $block->setShowAuthor(false);
        $block->setReadTime(false);
        $block->setShowCategory(false);
        $block->setShowComment(false);
        $block->setShowExcerpt(false);
        $block->setShowView(false);
        return $block->toHtml();
    }
}