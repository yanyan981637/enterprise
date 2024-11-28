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

namespace Magezon\Blog\Plugin\Controller\Post;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Model\Post;

class View
{
	/**
	 * @var StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var Session
	 */
	protected $_customerSession;

	/**
	 * @var Visitor
	 */
	protected $_customerVisitor;

	/**
	 * @var Registry
	 */
	protected $registry;

	/**
	 * @var ResourceConnection
	 */
	protected $_resource;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param Visitor $customerVisitor
     * @param Registry $registry
     * @param ResourceConnection $resource
     */
	public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        Visitor $customerVisitor,
        Registry $registry,
		ResourceConnection $resource
	) {
		$this->_storeManager = $storeManager;
		$this->_customerSession = $customerSession;
		$this->_customerVisitor = $customerVisitor;
		$this->registry = $registry;
		$this->_resource = $resource;
	}

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
	public function afterExecute(
		$subject,
		$result
	) {
		$post = $this->registry->registry('current_post');
		if ($post) $this->reportViews($post);
		return $result;
	}

	/**
	 * @param  Post $post
	 */
	public function reportViews($post)
	{
		$postId     = $post->getId();
		$visitorId  = $this->_customerVisitor->getId();
		$storeId    = $this->_storeManager->getStore()->getId();
		$customerId = $this->_customerSession->getId();
		$resource   = $this->_resource;
		$connection = $resource->getConnection();
		$table      = $resource->getTableName('mgz_blog_viewed_post_index');

		$select = $connection->select()
		->from($table)
		->where('post_id = ?', $postId)
		->where('visitor_id = ?', $visitorId)
		->where('store_id = ?', $storeId);

		$count = intval($connection->fetchOne($select));
		if (!$count) {
            $data  = [
				'post_id'     => (int)$postId,
				'visitor_id'  => (int)$visitorId,
				'store_id'    => (int)$storeId,
				'customer_id' => (int)$customerId
			];
			$insert = $resource->getConnection()->insert($table, $data);
			$totalViews = ((int)$post->getTotalViews()) + 1;
			$post->setTotalViews($totalViews);
			$post->save();
		}
	}
}