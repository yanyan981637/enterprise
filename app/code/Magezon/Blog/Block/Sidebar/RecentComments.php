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

namespace Magezon\Blog\Block\Sidebar;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Comment;
use Magezon\Blog\Model\ResourceModel\Comment\Collection;
use Magezon\Blog\Model\ResourceModel\Comment\CollectionFactory;

class RecentComments extends Template
{	
    /**
     * @var \Magento\Framework\App\Http\Context
     */
	protected $httpContext;

    /**
     * @var StringUtils
     */
    protected $string;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param StringUtils $string
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
	public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        StringUtils $string,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext       = $httpContext;
        $this->string            = $string;
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags'     => [Comment::CACHE_TAG]
        ]);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cache = [
            'MGZ_BLOG_SIDEBAR_RECENTCOMMENTS',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
        ];
        return $cache;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->_collection === NULL) {
            $numberOfComments = (int)$this->dataHelper->getConfig('sidebar/tabs/recent_comments/number_of_comments');
            $post = $this->getCurrentPost();
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('status', Comment::STATUS_APPROVED);
            $collection->addPostInformation();
            $collection->addCustomerInformation();
            $collection->setOrder('comment_id', 'DESC');
            $collection->setPageSize($numberOfComments);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @param  Comment $comment
     * @return string
     */
    public function getCommentContent($comment)
    {
    	return $this->string->substr(strip_tags($comment->getContent()), 0, 100);
    }
}