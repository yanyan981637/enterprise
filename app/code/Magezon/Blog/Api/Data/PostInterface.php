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

interface PostInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const POST_ID          = 'post_id';
    const IDENTIFIER       = 'identifier';
    const TITLE            = 'title';
    const PUBLISH_DATE     = 'publish_date';
    const IMAGE            = 'image';
    const CONTENT          = 'content';
    const EXCERPT          = 'excerpt';
    const IS_ACTIVE        = 'is_active';
    const AUTHOR_ID        = 'author_id';
    const TYPE             = 'type';
    const OG_TITLE         = 'og_title';
    const OG_DESCRIPTION   = 'og_description';
    const OG_IMG           = 'og_img';
    const OG_TYPE          = 'og_type';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const VIDEO_LINK       = 'video_link';
    const TOTAL_VIEWS      = 'total_views';
    const CREATION_TIME    = 'creation_time';
    const UPDATE_TIME      = 'update_time';
    const ALLOW_COMMENT    = 'allow_comment';
    const PAGE_LAYOUT      = 'page_layout';
    const FEATURED         = 'featured';
    const PINNED           = 'pinned';
    const CANONICAL_URL    = 'canonical_url';
    const LIKE_TOTAL       = 'like_total';
    const DISLIKE_TOTAL    = 'dislike_total';
    const READ_TIME        = 'read_time';
    const END_DATE         = 'end_date';
    const POSITION         = 'position';

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
     * @return PostInterface
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
     * @return PostInterface
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
     * @return PostInterface
     */
    public function setTitle($title);

    /**
     * Get publish date
     *
     * @return string|null
     */
    public function getPublishDate();

    /**
     * Set publish date
     *
     * @param string $publishDate
     * @return PostInterface
     */
    public function setPublishDate($publishDate);

    /**
     * Get image
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Set image
     *
     * @param string $image
     * @return PostInterface
     */
    public function setImage($image);

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
     * @return PostInterface
     */
    public function setContent($content);

    /**
     * Get excerpt
     *
     * @return string|null
     */
    public function getExcerpt();

    /**
     * Set excerpt
     *
     * @param string $excerpt
     * @return PostInterface
     */
    public function setExcerpt($excerpt);

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
     * @return PostInterface
     */
    public function setIsActive($isActive);

    /**
     * Get author id
     *
     * @return int|null
     */
    public function getAuthorId();

    /**
     * Set author id
     *
     * @param int $authorId
     * @return PostInterface
     */
    public function setAuthorId($authorId);

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return PostInterface
     */
    public function setType($type);

    /**
     * Get og title
     *
     * @return string|null
     */
    public function getOgTitle();

    /**
     * Set og title
     *
     * @param string $ogTitle
     * @return PostInterface
     */
    public function setOgTitle($ogTitle);

    /**
     * Get og description
     *
     * @return string|null
     */
    public function getOgDescription();

    /**
     * Set og description
     *
     * @param string $ogDescription
     * @return PostInterface
     */
    public function setOgDescription($ogDescription);

    /**
     * Get og type
     *
     * @return string|null
     */
    public function getOgType();

    /**
     * Set og type
     *
     * @param string $ogType
     * @return PostInterface
     */
    public function setOgType($ogType);

    /**
     * Get og img
     *
     * @return string|null
     */
    public function getOgImg();

    /**
     * Set og img
     *
     * @param string $ogImg
     * @return PostInterface
     */
    public function setOgImg($ogImg);

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
     * @return PostInterface
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
     * @return PostInterface
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
     * @return PostInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get video link
     *
     * @return string|null
     */
    public function getVideoLink();

    /**
     * Set video link
     *
     * @param string $videoLink
     * @return PostInterface
     */
    public function setVideoLink($videoLink);

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
     * @return PostInterface
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
     * @return PostInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Get total views
     *
     * @return int|null
     */
    public function getTotalViews();

    /**
     * Set total views
     *
     * @param int $totalViews
     * @return PostInterface
     */
    public function setTotalViews($totalViews);

    /**
     * Get allow comment
     *
     * @return int|null
     */
    public function getAllowComment();

    /**
     * Set allow comment
     *
     * @param int $allowComment
     * @return PostInterface
     */
    public function setAllowComment($allowComment);

    /**
     * Get page layout
     *
     * @return string|null
     */
    public function getPageLayout();

    /**
     * Set page layout
     *
     * @param string $pageLayout
     * @return PostInterface
     */
    public function setPageLayout($pageLayout);

    /**
     * Is featured
     *
     * @return bool|null
     */
    public function isFeatured();

    /**
     * Set is featured
     *
     * @param int|bool $featured
     * @return PostInterface
     */
    public function setFeatured($featured);

    /**
     * Is pinned
     *
     * @return bool|null
     */
    public function isPinned();

    /**
     * Set is pinned
     *
     * @param int|bool $pinned
     * @return PostInterface
     */
    public function setPinned($pinned);

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
     * @return PostInterface
     */
    public function setCanonicalUrl($canonicalUrl);

    /**
     * get Like Total
     * @return mixed
     */
    public function getLikeTotal();

    /**
     * set Like Total
     * @param $likeTotal
     * @return PostInterface
     */
    public function setLikeTotal($likeTotal);

    /**
     * get Dislike Total
     * @return mixed
     */
    public function getDislikeTotal();

    /**
     * set Dislike Total
     * @param $dislikeTotal
     * @return PostInterface
     */
    public function setDislikeTotal($dislikeTotal);

    /**
     * get Read Time
     * @return mixed
     */
    public function getReadTime();

    /**
     * set readTime
     * @param $readTime
     * @return PostInterface
     */
    public function setReadTime($readTime);

    /**
     * get End Date
     * @return mixed
     */
    public function getEndDate();

    /**
     * set End Date
     * @param $endDate
     * @return PostInterface
     */
    public function setEndDate($endDate);


    /**
     * get Position
     * @return mixed
     */
    public function getPosition();

    /**
     * set Position
     * @param $position
     * @return PostInterface
     */
    public function setPosition($position);

}
