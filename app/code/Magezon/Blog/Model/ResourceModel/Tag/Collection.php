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

namespace Magezon\Blog\Model\ResourceModel\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezon\Blog\Model\Tag;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'tag_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_tag_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'tag_collection';

    protected function _construct()
    {
        $this->_init(Tag::class, \Magezon\Blog\Model\ResourceModel\Tag::class);
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('is_active', (int)$isActive ? 1 : 0);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }

    public function addTotalPosts()
    {
        $this->getSelect()->joinLeft(
            ['mbtp' => $this->getTable('mgz_blog_tag_post')],
            'main_table.tag_id = mbtp.tag_id',
            ['total_posts' => 'COUNT(mbtp.tag_id)']
        )->group('main_table.tag_id');
        return $this;
    }
}