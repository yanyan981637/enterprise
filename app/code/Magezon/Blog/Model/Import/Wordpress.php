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

namespace Magezon\Blog\Model\Import;

use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\LocalizedException;
use Magezon\Blog\Model\Comment;

class Wordpress extends AbstractImport
{
    /**
     * @var string
     */
    protected $_platform = 'wordpress';

    /**
     * @var array
     */
    protected $_postmeta;

    /**
     * @var array
     */
    protected $_wpPosts;

    /**
     * @var array
     */
    protected $_wpCategories;

    /**
     * @var array
     */
    protected $_wpTags;

    /**
     * @var array
     */
    protected $_wpAuthors;

    /**
     * @var array
     */
    protected $_wpComments;

    /**
     * @var array
     */
    protected $_cachePosts = [];

    /**
     * @param  string $content
     * @return string
     */
    public function prepareContent($content)
    {
        $content = str_replace('<!--more-->', '<!-- pagebreak -->', $content);
        $content = preg_replace(
            '/src=[\'"]((http:\/\/|https:\/\/|\/\/)(.*)|(\s|"|\')|(\/[\d\w_\-\.]*))\/wp-content\/uploads(.*)((\.jpg|\.jpeg|\.gif|\.png|\.tiff|\.tif|\.svg)|(\s|"|\'))[\'"\s]/Ui',
            'src="$4{{media url="' . self::IMAGE_DIRECTORY . '$6$8"}}$9"',
            $content
        );
        return $content;
    }

    /**
     * @return array
     */
    protected function getWpCategories()
    {
        if ($this->_wpCategories == null) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['t' => $this->getTableName('terms')])
            ->joinLeft(
                ['tt' => $this->getTableName('term_taxonomy')],
                't.term_id = tt.term_id',
                ['*']
            )->where(
                'tt.taxonomy = ?',
                'category'
            )->where(
                't.slug <> ?',
                'uncategorized'
            );
            $this->_wpCategories = $connection->fetchAll($select);
        }
        return $this->_wpCategories;
    }

    protected function _importCategories()
    {
        $items    = $this->getWpCategories();
        $behavior = $this->getBehavior();
        $insert   = $update = $delete = [];

        foreach ($items as $item) {
            $_entity = $this->getCategory($item['slug']);

            if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
                if ($_entity) {
                    $delete[] = $_entity->getId();
                }
            }

            if (Import::BEHAVIOR_APPEND == $behavior || Import::BEHAVIOR_REPLACE == $behavior) {
                $new = $this->prepareCategoryData($item);

                if (Import::BEHAVIOR_APPEND == $behavior) {
                    if ($_entity) {
                        $update[] = $new;
                    } else {
                        $insert[] = $new;
                    }
                }

                if (Import::BEHAVIOR_REPLACE == $behavior) {
                    $insert[] = $new;
                }

                if ($_entity) {
                    $new = $_entity->getData();
                }
                $new['ID'] = $item['term_id'];
                $this->_importedCategories[] = $new;
            }
        }

        $this->_deleteCategories($delete);
        $this->_saveCategories($insert, $update);
    }

    /**
     * @param  array  $delete
     */
    protected function _deleteCategories(array $delete)
    {
        $connection = $this->resource->getConnection();
        $condition  = $connection->quoteInto('category_id IN (?)', $delete);
        $connection->delete($this->getCategoryTable(), $condition);
        return $this;
    }

    /**
     * @param array $insert
     * @param array $update
     * @throws LocalizedException
     */
    protected function _saveCategories(array $insert, array $update)
    {
        $connection = $this->resource->getConnection();
        if ($insert) {
            foreach ($insert as &$_category) {
                if ($this->getCategory($_category['identifier'])) {
                    throw new LocalizedException(__('Something went wrong while importing categories'));
                }
            }
            $wpCategories = $this->getWpCategories();
            foreach ($insert as &$row) {
                $parentId = 0;
                foreach ($wpCategories as $_row) {
                    if ($_row['term_id'] == $row['parent_id']) {
                        foreach ($insert as $row2) {
                            if ($row2['identifier'] == $_row['slug']) {
                                $parentId = $row2['category_id'];
                            }
                        }
                    }
                }
                $row['parent_id'] = $parentId;
            }
            $connection->insertMultiple($this->getCategoryTable(), $insert);
            foreach ($insert as $row1) {
                $data[] = [
                    'category_id' => $row1['category_id'],
                    'store_id'    => Store::DEFAULT_STORE_ID
                ];
            }
            $connection->insertMultiple($this->getCategoryStoreTable(), $data);
        }
        if ($update) {
            $connection->insertOnDuplicate(
                $this->getCategoryTable(),
                $update
            );
        }
        return $this;
    }

    /**
     * @param  array $category
     * @return array
     */
    public function prepareCategoryData($category)
    {
        $_cat = $this->getCategory($category['slug']);
        $categoryId = $this->_getNextCategoryId();
        if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_cat) {
            $categoryId = $_cat->getId();
        }
        $data = [
            'category_id'      => $categoryId,
            'identifier'       => $category['slug'],
            'title'            => $category['name'],
            'content'          => $this->prepareContent($category['description']),
            'is_active'        => 1,
            'parent_id'        => $category['parent'],
            'include_in_menu'  => 1,
            'position'         => 0,
            'meta_title'       => '',
            'meta_keywords'    => '',
            'meta_description' => '',
            'list_layout'      => 'fixed_thumb',
            'page_layout'      => '2columns-right',
            'grid_col'         => 3,
            'canonical_url'    => ''
        ];
        return $data;
    }

    /**
     * @return array
     */
    protected function getWpTags()
    {
        if ($this->_wpTags == null) {
            $connection = $this->getConnection();
            $select     = $connection->select()->from(['t' => $this->getTableName('terms')])
            ->joinLeft(
                ['tt' => $this->getTableName('term_taxonomy')],
                't.term_id = tt.term_id',
                ['*']
            )->where(
                'tt.taxonomy = ?',
                'post_tag'
            );
            $this->_wpTags = $connection->fetchAll($select);
        }
        return $this->_wpTags;
    }

    protected function _importTags()
    {
        $items = $this->getWpTags();
        $behavior = $this->getBehavior();
        $insert = $update = $delete = [];

        foreach ($items as $item) {
            $_entity = $this->getTag($item['slug']);

            if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
                if ($_entity) {
                    $delete[] = $_entity->getId();
                }
            }

            if (Import::BEHAVIOR_APPEND == $behavior || Import::BEHAVIOR_REPLACE == $behavior) {
                $new = $this->prepareTagData($item);

                if (Import::BEHAVIOR_APPEND == $behavior) {
                    if ($_entity) {
                        $update[] = $new;
                    } else {
                        $insert[] = $new;
                    }
                }

                if (Import::BEHAVIOR_REPLACE == $behavior) {
                    $insert[] = $new;
                }

                if ($_entity) {
                    $new = $_entity->getData();
                }
                $new['ID'] = $item['term_id'];
                $this->_importedTags[] = $new;
            }
        }

        $this->_deleteTags($delete);
        $this->_saveTags($insert, $update);
    }

    /**
     * @param  array $tag
     * @return array
     */
    public function prepareTagData($tag)
    {
        $_tag  = $this->getTag($tag['slug']);
        $tagId = $this->_getNextTagId();
        if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_tag) {
            $tagId = $_tag->getId();
        }
        $data = [
            'tag_id'           => $tagId,
            'identifier'       => $tag['slug'],
            'title'            => $tag['name'],
            'content'          => $this->prepareContent($tag['description']),
            'is_active'        => 1,
            'meta_title'       => '',
            'meta_keywords'    => '',
            'meta_description' => '',
            'canonical_url'    => ''
        ];
        return $data;
    }

    /**
     * @param  array  $delete
     */
    protected function _deleteTags(array $delete)
    {
        $connection = $this->resource->getConnection();
        $condition  = $connection->quoteInto('tag_id IN (?)', $delete);
        $connection->delete($this->getTagTable(), $condition);
        return $this;
    }

    /**
     * @param array $insert
     * @param array $update
     * @throws LocalizedException
     */
    protected function _saveTags(array $insert, array $update)
    {
        $connection = $this->resource->getConnection();
        if ($insert) {
            foreach ($insert as $_tag) {
                if ($this->getTag($_tag['identifier'])) {
                    throw new LocalizedException(__('Something went wrong while importing tags'));
                }
            }
            $connection->insertMultiple($this->getTagTable(), $insert);
        }
        if ($update) {
            $connection->insertOnDuplicate(
                $this->getTagTable(),
                $update
            );
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getWpAuthors()
    {
        if ($this->_wpAuthors == null) {
            $connection = $this->getConnection();
            $select     = $connection->select()->from(['u1' => $this->getTableName('users')])
            ->joinLeft(
                ['u2' => $this->getTableName('usermeta')],
                'u1.ID = u2.user_id AND u2.meta_key = "first_name"',
                ['first_name' => 'meta_value']
            )->joinLeft(
                ['u3' => $this->getTableName('usermeta')],
                'u1.ID = u3.user_id AND u3.meta_key = "last_name"',
                ['last_name' => 'meta_value']
            )->joinLeft(
                ['u4' => $this->getTableName('usermeta')],
                'u1.ID = u4.user_id AND u4.meta_key = "nickname"',
                ['nickname' => 'meta_value']
            )->joinLeft(
                ['u5' => $this->getTableName('usermeta')],
                'u1.ID = u5.user_id AND u5.meta_key = "description"',
                ['description' => 'meta_value']
            );
            $this->_wpAuthors = $connection->fetchAll($select);
        }
        return $this->_wpAuthors;
    }

    protected function _importAuthors()
    {
        $items = $this->getWpAuthors();
        $behavior = $this->getBehavior();
        $insert = $update = $delete = [];

        foreach ($items as $item) {
            $_entity = $this->getAuthor($item['user_login']);

            if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
                if ($_entity) {
                    $delete[] = $_entity->getId();
                }
            }

            if (Import::BEHAVIOR_APPEND == $behavior || Import::BEHAVIOR_REPLACE == $behavior) {
                $new = $this->prepareAuthorData($item);

                if (Import::BEHAVIOR_APPEND == $behavior) {
                    if ($_entity) {
                        $update[] = $new;
                    } else {
                        $insert[] = $new;
                    }
                }

                if (Import::BEHAVIOR_REPLACE == $behavior) {
                    $insert[] = $new;
                }

                if ($_entity) {
                    $new = $_entity->getData();
                }
                $new['ID'] = $item['ID'];
                $this->_importedAuthors[] = $new;
            }
        }

        $this->_deleteAuthors($delete);
        $this->_saveAuthors($insert, $update);
    }

    /**
     * @param  array $author
     * @return array
     */
    public function prepareAuthorData($author)
    {
        $_author  = $this->getAuthor($author['user_login']);
        $authorId = $this->_getNextAuthorId();
        if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_author) {
            $authorId = $_author->getId();
        }
        $data = [
            'author_id'        => $authorId,
            'identifier'       => $author['user_login'],
            'first_name'       => $author['first_name'],
            'last_name'        => $author['last_name'],
            'email'            => $author['user_email'],
            'nickname'         => $author['nickname'],
            'display_name'     => \Magezon\Blog\Model\Author::DISPLAY_FL,
            'image'            => '',
            'content'          => $author['description'],
            'short_content'    => '',
            'twitter'          => '',
            'facebook'         => '',
            'linkedin'         => '',
            'flickr'           => '',
            'youtube'          => '',
            'pinterest'        => '',
            'behance'          => '',
            'instagram'        => '',
            'meta_title'       => '',
            'meta_keywords'    => '',
            'meta_description' => '',
            'user_id'          => 0,
            'is_active'        => 1,
        ];
        return $data;
    }

    /**
     * @param  array  $delete
     */
    protected function _deleteAuthors(array $delete)
    {
        $connection = $this->resource->getConnection();
        $condition  = $connection->quoteInto('author_id IN (?)', $delete);
        $connection->delete($this->getAuthorTable(), $condition);
        return $this;
    }

    /**
     * @param array $insert
     * @param array $update
     * @throws LocalizedException
     */
    protected function _saveAuthors(array $insert, array $update)
    {
        $connection = $this->resource->getConnection();
        if ($insert) {
            foreach ($insert as $_author) {
                if ($this->getAuthor($_author['identifier'])) {
                    throw new LocalizedException(__('Something went wrong while importing authors'));
                }
            }
            $connection->insertMultiple($this->getAuthorTable(), $insert);
        }
        if ($update) {
            $connection->insertOnDuplicate(
                $this->getAuthorTable(),
                $update
            );
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getWpPosts()
    {
        if ($this->_wpPosts == null) {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getTableName('posts'))
                ->where('post_type = ?', 'post')->where('post_status <> ?', 'auto-draft')
                ->where('post_status <> ?', 'draft');
            $this->_wpPosts = $connection->fetchAll($select);
        }
        return $this->_wpPosts;
    }

    protected function _importPosts()
    {
        $items = $this->getWpPosts();
        $behavior = $this->getBehavior();
        $insert = $update = $delete = [];

        foreach ($items as $item) {
            $_entity = $this->getPost($item['post_name']);

            if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
                if ($_entity) {
                    $delete[] = $_entity->getId();
                }
            }

            if (Import::BEHAVIOR_APPEND == $behavior || Import::BEHAVIOR_REPLACE == $behavior) {
                $new = $this->preparePostData($item);

                if (Import::BEHAVIOR_APPEND == $behavior) {
                    if ($_entity) {
                        $update[] = $new;
                    } else {
                        $insert[] = $new;
                    }
                }

                if (Import::BEHAVIOR_REPLACE == $behavior) {
                    $insert[] = $new;
                }

                if ($_entity) {
                    $new = $_entity->getData();
                }
                $new['ID'] = $item['ID'];
                $this->_importedPosts[] = $new;
            }
        }

        $this->_deletePosts($delete);
        $this->_savePosts($insert, $update);
        $this->_importCategoriesPosts();
        $this->_importTagsPosts();
    }

    /**
     * @return array
     */
    protected function getPostMeta()
    {
        if ($this->_postmeta == null) {
            $connection = $this->getConnection();
            $this->_postmeta = $connection->fetchAll($connection->select()->from($this->getTableName('postmeta')));
        }
        return $this->_postmeta;
    }

    /**
     * @return string
     */
    protected function getPostFeaturedImage($post)
    {
        $image = '';
        $postMeta  = $this->getPostMeta();
        foreach ($postMeta as $_row) {
            if (($_row['post_id'] == $post['ID']) && $_row['meta_key'] == '_thumbnail_id') {
                $metaId = $_row['meta_value'];
                foreach ($postMeta as $_row2) {
                    if ($_row2['post_id'] == $metaId && $_row2['meta_key'] == '_wp_attached_file') {
                        $image = $_row2['meta_value'];
                        break;
                    }
                }
            }
            if ($image) {
                break;
            }
        }
        if ($image) {
            $image = self::IMAGE_DIRECTORY . '/' . $image;
        }
        return $image;
    }

    /**
     * @return int
     */
    protected function _getPostAuthorId($wpAuthorId)
    {
        $authorId = 0;
        $importedAuthors = $this->getImportedAuthors();
        foreach ($importedAuthors as $_author) {
            if ($_author['ID'] == $wpAuthorId) {
                $authorId = $_author['author_id'];
                break;
            }
        }
        return $authorId;
    }

    /**
     * @param  array $post
     * @return array
     */
    public function preparePostData($post)
    {
        $authorId     = 0;
        $identifier   = $post['post_name'];
        $creationTime = $post['post_date_gmt'];
        $_post        = $this->getPost($identifier);
        $postId       = $this->_getNextPostId();
        if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_post) {
            $postId = $_post->getId();
        }

        $importedAuthors = $this->getImportedAuthors();

        $data = [
            'post_id'          => $postId,
            'identifier'       => $identifier,
            'title'            => $post['post_title'],
            'content'          => $this->prepareContent($post['post_content']),
            'excerpt'          => '',
            'is_active'        => (int)($post['post_status'] == 'publish'),
            'author_id'        => $this->_getPostAuthorId($post['post_author']),
            'total_views'      => 0,
            'og_title'         => '',
            'og_description'   => '',
            'og_img'           => '',
            'og_type'          => '',
            'image'            => $this->getPostFeaturedImage($post),
            'meta_title'       => '',
            'meta_keywords'    => '',
            'meta_description' => '',
            'video_link'       => '',
            'type'             => 'image',
            'publish_date'     => $creationTime,
            'creation_time'    => $creationTime,
            'update_time'      => $post['post_modified_gmt'],
            'allow_comment'    => 1,
            'page_layout'      => '2columns-right',
            'featured'         => 0,
            'pinned'           => 0,
            'canonical_url'    => ''
        ];

        return $data;
    }

    /**
     * @param  array  $delete
     */
    protected function _deletePosts(array $delete)
    {
        $connection = $this->resource->getConnection();
        $condition  = $connection->quoteInto('post_id IN (?)', $delete);
        $connection->delete($this->getPostTable(), $condition);
        return $this;
    }

    /**
     * @param array $insert
     * @param array $update
     * @throws LocalizedException
     */
    protected function _savePosts(array $insert, array $update)
    {
        $connection = $this->resource->getConnection();
        if ($insert) {
            foreach ($insert as $_post) {
                if ($this->getPost($_post['identifier'])) {
                    throw new LocalizedException(__('Something went wrong while importing posts'));
                }
            }
            $connection->insertMultiple($this->getPostTable(), $insert);
            $data = [];
            foreach ($insert as $row) {
                $data[] = [
                    'post_id'  => (int)$row['post_id'],
                    'store_id' => Store::DEFAULT_STORE_ID
                ];
            }
            $connection->insertMultiple($this->getPostStoreTable(), $data);

            $data = [];
            $customerGroups = $this->getCustomGroups();
            foreach ($customerGroups as $group) {
                foreach ($insert as $row) {
                    $data[] = [
                        'post_id'           => $row['post_id'],
                        'customer_group_id' => $group->getId()
                    ];
                }
            }
            $connection->insertMultiple($this->getPostCustomerGroupTable(), $data);
        }
        if ($update) {
            $connection->insertOnDuplicate(
                $this->getPostTable(),
                $update
            );
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function _getWpPosts($taxonomy)
    {
        if (!isset($this->_cachePosts[$taxonomy])) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['p' => $this->getTableName('posts')])
            ->joinLeft(
                ['tr' => $this->getTableName('term_relationships')],
                'p.ID = tr.object_id',
                ['*']
            )->joinLeft(
                ['tx' => $this->getTableName('term_taxonomy')],
                'tr.term_taxonomy_id = tx.term_taxonomy_id',
                ['*']
            )->where(
                'p.post_type = ?',
                'post'
            )->where(
                'tx.taxonomy = ?',
                $taxonomy
            );
            $this->_cachePosts[$taxonomy] = $connection->fetchAll($select);
        }
        return $this->_cachePosts[$taxonomy];
    }

    /**
     * @return array
     */
    protected function _getTermPosts($taxonomy, $id)
    {
        $ids           = [];
        $posts         = $this->_getWpPosts($taxonomy);
        $importedPosts = $this->getImportedPosts();
        foreach ($posts as $row) {
            if ($row['term_id'] == $id) {
                foreach ($importedPosts as $_post) {
                    if ($_post['ID'] == $row['ID']) {
                        $ids[] = $_post['post_id'];
                        break;
                    }
                }
            }
        }
        return $ids;
    }

    protected function _importCategoriesPosts()
    {
        $delete             = [];
        $importedPosts      = $this->getImportedPosts();
        $importedCategories = $this->getImportedCategories();
        $data               = [];
        foreach ($importedCategories as $_row) {
            $posts = $this->_getTermPosts('category', $_row['ID']);
            foreach ($posts as $_id) {
                $delete[] = $_row['category_id'];
                $data[] = [
                    'category_id' => $_row['category_id'],
                    'post_id'     => $_id,
                    'position'    => 0
                ];
            }
        }
        $connection = $this->resource->getConnection();
        $condition  = $connection->quoteInto('category_id IN (?)', $delete);
        $connection->delete($this->getCategoryPostTable(), $condition);

        $connection = $this->resource->getConnection();
        if ($data) {
            $connection->insertMultiple($this->getCategoryPostTable(), $data);
        }
    }

    protected function _importTagsPosts()
    {
        $delete        = [];
        $importedPosts = $this->getImportedPosts();
        $importedTags  = $this->getImportedTags();
        $data          = [];
        foreach ($importedTags as $_row) {
            $posts = $this->_getTermPosts('post_tag', $_row['ID']);
            foreach ($posts as $_id) {
                $delete[] = $_row['tag_id'];
                $data[] = [
                    'tag_id'  => $_row['tag_id'],
                    'post_id' => $_id
                ];
            }
        }
        $connection = $this->resource->getConnection();
        $condition  = $connection->quoteInto('tag_id IN (?)', $delete);
        $connection->delete($this->getTagPostTable(), $condition);

        $connection = $this->resource->getConnection();
        if ($data) {
            $connection->insertMultiple($this->getTagPostTable(), $data);
        }
    }

    /**
     * @return array
     */
    protected function getWpComments()
    {
        if ($this->_wpComments == null) {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getTableName('comments'))
            ->where(
                'comment_approved = ?',
                1
            )->orWhere(
                'comment_approved = ?',
                0
            )->order('comment_ID DESC');
            $this->_wpComments = $connection->fetchAll($select);
        }
        return $this->_wpComments;
    }

    protected function _importComments()
    {
        $posts    = $this->getImportedPosts();
        $comments = $this->getWpComments();
        $insert   = [];
        foreach ($comments as $_comment) {
            if ($item = $this->prepareCommentData($_comment)) {
                $insert[] = $item;
            }
        }
        $this->_saveComments($insert);
    }

    /**
     * @param  array $post
     * @return array
     */
    public function prepareCommentData($comment)
    {
        $postId = 0;
        $posts  = $this->getImportedPosts();

        foreach ($posts as $_post) {
            if ($_post['ID'] == $comment['comment_post_ID']) {
                $postId = $_post['post_id'];
            }
        }

        if (!$postId) {
            return;
        }

        switch ($comment['comment_approved']) {
            case '1':
                $status = Comment::STATUS_APPROVED;
                break;

            default:
                $status = Comment::STATUS_PENDING;
                break;
        }

        $data = [
            'comment_id'    => $this->_getNextCommentId(),
            'post_id'       => $postId,
            'parent_id'     => $comment['comment_parent'],
            'content'       => $comment['comment_content'],
            'author'        => $comment['comment_author'],
            'author_email'  => $comment['comment_author_email'],
            'customer_id'   => 0,
            'store_id'      => Store::DEFAULT_STORE_ID,
            'status'        => $status,
            'remote_ip'     => $comment['comment_author_IP'],
            'brower'        => $comment['comment_agent'],
            'creation_time' => $comment['comment_date'],
            'ID'            => $comment['comment_ID']
        ];
        return $data;
    }

    public function _saveComments($insert)
    {
        if ($insert) {
            $inserted = $insert;
            $connection = $this->resource->getConnection();
            $wpComments = $this->getWpComments();
            foreach ($insert as &$row) {
                $parentId = 0;
                if ($row['parent_id']) {
                    foreach ($inserted as $row1) {
                        if ($row1['ID'] == $row['parent_id']) {
                            $parentId = $row1['comment_id'];
                            break;
                        }
                    }
                }
                unset($row['ID']);
                $row['parent_id'] = $parentId;
            }
            $connection->insertMultiple($this->getCommentTable(), $insert);
            $this->_importedComments = $inserted;
        }
    }
}
