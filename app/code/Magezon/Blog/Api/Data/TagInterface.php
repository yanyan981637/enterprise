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

interface TagInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const TAG_ID           = 'tag_id';
    const IDENTIFIER       = 'identifier';
    const TITLE            = 'title';
    const CONTENT          = 'content';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const CREATION_TIME    = 'creation_time';
    const UPDATE_TIME      = 'update_time';
    const IS_ACTIVE        = 'is_active';
    const CANONICAL_URL    = 'canonical_url';
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
     * @return TagInterface
     */
    public function setId($id);

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return TagInterface
     */
    public function setIdentifier($identifier);

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return TagInterface
     */
    public function setTitle($title);

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
     * @return TagInterface
     */
    public function setContent($content);

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return TagInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return TagInterface
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return TagInterface
     */
    public function setMetaDescription($metaDescription);

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
     * @return TagInterface
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
     * @return TagInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return TagInterface
     */
    public function setIsActive($isActive);

    /**
     * Get canonical url
     *
     * @return string|null
     */
    public function getCanonicalUrl();

    /**
     * Set canonical url
     *
     * @param string $canonicalUrl
     * @return TagInterface
     */
    public function setCanonicalUrl($canonicalUrl);
}
