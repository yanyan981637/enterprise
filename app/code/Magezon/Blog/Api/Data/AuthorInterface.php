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

interface AuthorInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const AUTHOR_ID        = 'author_id';
    const IDENTIFIER       = 'identifier';
    const FIRST_NAME       = 'first_name';
    const LAST_NAME        = 'last_name';
    const NICKNAME         = 'nickname';
    const DISPLAY_NAME     = 'display_name';
    const IMAGE            = 'image';
    const CONTENT          = 'content';
    const SHORT_CONTENT    = 'short_content';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const TWITTER          = 'twitter';
    const FACEBOOK         = 'facebook';
    const LINKEDIN         = 'linkedin';
    const FLICKR           = 'flickr';
    const YOUTUBE          = 'youtube';
    const PINTEREST        = 'pinterest';
    const BEHANCE          = 'behance';
    const INSTAGRAM        = 'instagram';
    const CREATION_TIME    = 'creation_time';
    const UPDATE_TIME      = 'update_time';
    const IS_ACTIVE        = 'is_active';
    const USER_ID          = 'user_id';
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
     * @return AuthorInterface
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
     * @return AuthorInterface
     */
    public function setIdentifier($identifier);

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstName();

    /**
     * Set first name
     *
     * @param string $firstName
     * @return AuthorInterface
     */
    public function setFirstName($firstName);

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastName();

    /**
     * Set last name
     *
     * @param string $lastName
     * @return AuthorInterface
     */
    public function setLastName($lastName);

    /**
     * Get nick name
     *
     * @return string|null
     */
    public function getNickname();

    /**
     * Set nick name
     *
     * @param string $nickname
     * @return AuthorInterface
     */
    public function setNickname($nickname);

    /**
     * Get display name
     *
     * @return string|null
     */
    public function getDisplayName();

    /**
     * Set display name
     *
     * @param string $displayName
     * @return AuthorInterface
     */
    public function setDisplayName($displayName);

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
     * @return AuthorInterface
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
     * @param string $description
     * @return AuthorInterface
     */
    public function setContent($description);

    /**
     * Get short content
     *
     * @return string|null
     */
    public function getShortContent();

    /**
     * Set short content
     *
     * @param string $shortContent
     * @return AuthorInterface
     */
    public function setShortContent($shortContent);

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
     * @return AuthorInterface
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
     * @return AuthorInterface
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
     * @return AuthorInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get twitter
     *
     * @return string|null
     */
    public function getTwitter();

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return AuthorInterface
     */
    public function setTwitter($twitter);

    /**
     * Get facebook
     *
     * @return string|null
     */
    public function getFacebook();

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return AuthorInterface
     */
    public function setFacebook($facebook);

    /**
     * Get linkedin
     *
     * @return string|null
     */
    public function getLinkedin();

    /**
     * Set linkedin
     *
     * @param string $linkedin
     * @return AuthorInterface
     */
    public function setLinkedin($linkedin);

    /**
     * Get flickr
     *
     * @return string|null
     */
    public function getFlickr();

    /**
     * Set flickr
     *
     * @param string $flickr
     * @return AuthorInterface
     */
    public function setFlickr($flickr);

    /**
     * Get youtube
     *
     * @return string|null
     */
    public function getYoutube();

    /**
     * Set youtube
     *
     * @param string $youtube
     * @return AuthorInterface
     */
    public function setYoutube($youtube);

    /**
     * Get pinterest
     *
     * @return string|null
     */
    public function getPinterest();

    /**
     * Set pinterest
     *
     * @param string $pinterest
     * @return AuthorInterface
     */
    public function setPinterest($pinterest);

    /**
     * Get behance
     *
     * @return string|null
     */
    public function getBehance();

    /**
     * Set behance
     *
     * @param string $behance
     * @return AuthorInterface
     */
    public function setBehance($behance);

    /**
     * Get instagram
     *
     * @return string|null
     */
    public function getInstagram();

    /**
     * Set instagram
     *
     * @param string $instagram
     * @return AuthorInterface
     */
    public function setInstagram($instagram);

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
     * @return AuthorInterface
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
     * @return AuthorInterface
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
     * @return AuthorInterface
     */
    public function setIsActive($isActive);

    /**
     * Get user id
     *
     * @return int|null
     */
    public function getUserId();

    /**
     * Set user id
     *
     * @param int $userId
     * @return AuthorInterface
     */
    public function setUserId($userId);
}
