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

interface CategoryInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CATEGORY_ID      = 'category_id';
    const IDENTIFIER       = 'identifier';
    const TITLE            = 'title';
    const CONTENT          = 'content';
    const PARENT_ID        = 'parent_id';
    const IS_ACTIVE        = 'is_active';
    const INCLUDE_IN_MENU  = 'include_in_menu';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const POSITION         = 'position';
    const CREATION_TIME    = 'creation_time';
    const UPDATE_TIME      = 'update_time';
    const LIST_LAYOUT      = 'list_layout';
    const GRID_COL         = 'grid_col';
    const PAGE_LAYOUT      = 'page_layout';
    const CANONICAL_URL    = 'canonical_url';
    const POSTS_SORT_BY    = 'posts_sort_by';
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
     * @return CategoryInterface
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
     * @return CategoryInterface
     */
    public function setIdentifier($identifier);

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return CategoryInterface
     */
    public function setTitle($title);

    /**
     * Get content
     *
     * @return string
     */
    public function getContent();

    /**
     * Set content
     *
     * @param string $content
     * @return CategoryInterface
     */
    public function setContent($content);

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
     * @return CategoryInterface
     */
    public function setParentId($parentId);

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
     * @return CategoryInterface
     */
    public function setIsActive($isActive);

    /**
     * Get includeInMenu
     *
     * @return int|null
     */
    public function getIncludeInMenu();

    /**
     * Set includeInMenu
     *
     * @param int $includeInMenu
     * @return CategoryInterface
     */
    public function setIncludeInMenu($includeInMenu);

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
     * @return CategoryInterface
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
     * @return CategoryInterface
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
     * @return CategoryInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return CategoryInterface
     */
    public function setPosition($position);

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
     * @return CategoryInterface
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
     * @return CategoryInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Get list layout
     *
     * @return string
     */
    public function getListLayout();

    /**
     * Set list layout
     *
     * @param string $listLayout
     * @return CategoryInterface
     */
    public function setListLayout($listLayout);

    /**
     * Get grid col
     *
     * @return int|null
     */
    public function getGridCol();

    /**
     * Set grid col
     *
     * @param int $gridCol
     * @return CategoryInterface
     */
    public function setGridCol($gridCol);

    /**
     * Get page layout
     *
     * @return string
     */
    public function getPageLayout();

    /**
     * Set page layout
     *
     * @param string $pageLayout
     * @return CategoryInterface
     */
    public function setPageLayout($pageLayout);

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
     * @return CategoryInterface
     */
    public function setCanonicalUrl($canonicalUrl);

    /**
     * Get posts sort by
     *
     * @return int|null
     */
    public function getPostsSortBy();

    /**
     * Set posts sort by
     *
     * @param int $postsSortBy
     * @return CategoryInterface
     */
    public function setPostsSortBy($postsSortBy);
}