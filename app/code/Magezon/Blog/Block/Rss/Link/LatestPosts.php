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

namespace Magezon\Blog\Block\Rss\Link;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\Blog\Helper\Data;

class LatestPosts extends Template
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->dataHelper->getConfig('rss/latest_posts')) return;
        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->dataHelper->getLatestPostsRssLink();
    }
}