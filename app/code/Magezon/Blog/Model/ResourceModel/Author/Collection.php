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

namespace Magezon\Blog\Model\ResourceModel\Author;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezon\Blog\Model\Author;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'author_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'blog_author_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'author_collection';

    protected function _construct()
    {
        $this->_init(Author::class, \Magezon\Blog\Model\ResourceModel\Author::class);
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

    /**
     * @return $this
     */
    public function addTotalPosts()
    {
        $this->getSelect()->joinLeft(
            ['mbp' => $this->getTable('mgz_blog_post')],
            'main_table.author_id = mbp.author_id',
            ['total_posts' => 'COUNT(mbp.author_id)']
        )->group('main_table.author_id');
        return $this;
    }
}