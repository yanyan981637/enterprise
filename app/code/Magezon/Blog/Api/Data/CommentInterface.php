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

namespace Magezon\Blog\Api\Data;

interface CommentInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const COMMENT_ID    = 'comment_id';
    const POST_ID       = 'post_id';
    const PARENT_ID     = 'parent_id';
    const AUTHOR        = 'author';
    const AUTHOR_EMAIL  = 'author_email';
    const CUSTOMER_ID   = 'customer_id';
    const STORE_ID      = 'store_id';
    const CONTENT       = 'content';
    const STATUS        = 'status';
    const REMOTE_IP     = 'remote_ip';
    const BROWER        = 'brower';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return CommentInterface
     */
    public function setId($id);

    /**
     * Get post id
     *
     * @return int|null
     */
    public function getPostId();

    /**
     * Set post id
     *
     * @param int $postId
     * @return CommentInterface
     */
    public function setPostId($postId);

    /**
     * Get parent id
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Set parent id
     *
     * @param int $parentId
     * @return CommentInterface
     */
    public function setParentId($parentId);

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor();

    /**
     * Set author
     *
     * @param string $author
     * @return CommentInterface
     */
    public function setAuthor($author);

    /**
     * Get author email
     *
     * @return string
     */
    public function getAuthorEmail();

    /**
     * Set author email
     *
     * @param string $authorEmail
     * @return CommentInterface
     */
    public function setAuthorEmail($authorEmail);

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return CommentInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return CommentInterface
     */
    public function setStoreId($storeId);

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     *
     * @param string $content
     * @return CommentInterface
     */
    public function setContent($content);

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return CommentInterface
     */
    public function setStatus($status);

    /**
     * Get remote ip
     *
     * @return string|null
     */
    public function getRemoteIp();

    /**
     * Set remote ip
     *
     * @param string $remoteIp
     * @return CommentInterface
     */
    public function setRemoteIp($remoteIp);

    /**
     * Get brower
     *
     * @return string|null
     */
    public function getBrower();

    /**
     * Set brower
     *
     * @param string $brower
     * @return CommentInterface
     */
    public function setBrower($brower);

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return CommentInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return CommentInterface
     */
    public function setUpdateTime($updateTime);
}
