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

class Amasty extends AbstractImport
{
	/**
	 * @var string
	 */
	protected $_platform = 'magento';

	/**
	 * @var array
	 */
	protected $_amastyPosts;

	/**
	 * @var array
	 */
	protected $_amastyCategories;

	/**
	 * @var array
	 */
	protected $_amastyTags;

	/**
	 * @var array
	 */
	protected $_amastyAuthors;

	/**
	 * @var array
	 */
	protected $_amastyComments;

	/**
	 * @var array
	 */
	protected $_cachePosts = [];

	/**
	 * @return array
	 */
	protected function getAmastyCategories()
	{
		if ($this->_amastyCategories == NULL) {
			$connection = $this->getConnection();
			$select = $connection->select()->from($this->getTableName('amasty_blog_categories'));
			$result = $connection->fetchAll($select);
			$categories = [];
			foreach ($result as $_cat) {
				if ($_cat['level']!=0) {
					$categories[] = $_cat;
				}
			}
			$this->_amastyCategories = $categories;
		}
		return $this->_amastyCategories;
	}

	protected function _importCategories()
	{
		$items    = $this->getAmastyCategories();
		$behavior = $this->getBehavior();
		$insert   = $update = $delete = [];

		foreach ($items as $item) {
			$_entity = $this->getCategory($item['url_key']);

			if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
				if ($_entity) $delete[] = $_entity->getId();
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

				if ($_entity) $new = $_entity->getData();
    			$new['ID'] = $item['category_id'];
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
    		$amastyCategories = $this->getAmastyCategories();
    		foreach ($insert as &$row) {
    			$parentId = 0;
    			foreach ($amastyCategories as $_row) {
    				if ($_row['category_id'] == $row['parent_id']) {
    					foreach ($insert as $row2) {
    						if ($row2['identifier'] == $_row['url_key']) {
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
    	$_cat = $this->getCategory($category['url_key']);
    	$categoryId = $this->_getNextCategoryId();
    	if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_cat) {
    		$categoryId = $_cat->getId();
    	}
    	$data = [
			'category_id'      => $categoryId,
			'identifier'       => $category['url_key'],
			'title'            => $category['name'],
			'content'          => '',
			'is_active'        => $category['status'],
			'parent_id'        => $category['parent_id'],
			'include_in_menu'  => 1,
			'position'         => $category['sort_order'],
			'meta_title'       => $category['meta_title'],
			'meta_keywords'    => $category['meta_tags'],
			'meta_description' => $category['meta_description'],
			'update_time'      => $category['updated_at'],
			'creation_time'    => $category['created_at'],
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
    protected function getAmastyTags()
    {
    	if ($this->_amastyTags == NULL) {
    		$connection = $this->getConnection();
			$select = $connection->select()->from($this->getTableName('amasty_blog_tags')); 
    		$this->_amastyTags = $connection->fetchAll($select);
    	}
    	return $this->_amastyTags;
    }

    protected function _importTags()
    {
		$items = $this->getAmastyTags();
		$behavior = $this->getBehavior();
		$insert = $update = $delete = [];

    	foreach ($items as $item) {
    		$_entity = $this->getTag($item['url_key']);

    		if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
    			if ($_entity) $delete[] = $_entity->getId();
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

    			if ($_entity) $new = $_entity->getData();
    			$new['ID'] = $item['tag_id'];
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
    	$_tag  = $this->getTag($tag['url_key']);
    	$tagId = $this->_getNextTagId();
    	if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_tag) {
    		$tagId = $_tag->getId();
    	}
    	$data = [
			'tag_id'           => $tagId,
			'identifier'       => $tag['url_key'],
			'title'            => $tag['name'],
			'content'          => '',
			'is_active'        => 1,
			'meta_title'       => $tag['meta_title'],
			'meta_keywords'    => $tag['meta_tags'],
			'meta_description' => $tag['meta_description'],
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
    protected function getAmastyAuthors()
    {
    	if ($this->_amastyAuthors == NULL) {
    		$connection = $this->getConnection();
    		$select     = $connection->select()->from($this->getTableName('amasty_blog_author'));
    		$this->_amastyAuthors = $connection->fetchAll($select);
    	}
    	return $this->_amastyAuthors;
    }

    protected function _importAuthors()
    {
    	$items = $this->getAmastyAuthors();
    	$behavior = $this->getBehavior();
    	$insert = $update = $delete = [];

    	foreach ($items as $item) {
    		$_entity = $this->getAuthor($item['url_key']);

    		if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
    			if ($_entity) $delete[] = $_entity->getId();
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

    			if ($_entity) $new = $_entity->getData();
    			$new['ID'] = $item['author_id'];
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
		$_author  = $this->getAuthor($author['url_key']);
		$authorId = $this->_getNextAuthorId();
		if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_author) {
			$authorId = $_author->getId();
		}
		$names = explode(' ', $author['name']);
		$data = [
			'author_id'        => $authorId,
			'identifier'       => $author['url_key'],
			'first_name'       => isset($names[0]) ? $names[0] : '',
			'last_name'        => isset($names[1]) ? $names[1] : '',
			'email'            => '',
			'nickname'         => $author['name'],
			'display_name'     => \Magezon\Blog\Model\Author::DISPLAY_FL,
			'image'            => '',
			'content'          => '',
			'short_content'    => '',
			'twitter'          => $author['twitter_profile'],
			'facebook'         => $author['facebook_profile'],
			'linkedin'         => '',
			'flickr'           => '',
			'youtube'          => '',
			'pinterest'        => '',
			'behance'          => '',
			'instagram'        => '',
			'meta_title'       => $author['meta_title'],
			'meta_keywords'    => $author['meta_tags'],
			'meta_description' => $author['meta_description'],
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
    protected function getAmastyPosts()
    {
    	if ($this->_amastyPosts == NULL) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from($this->getTableName('amasty_blog_posts'));
    		$this->_amastyPosts = $connection->fetchAll($select);
    	}
    	return $this->_amastyPosts;
    }

    protected function _importPosts()
    {
    	$items = $this->getAmastyPosts();
    	$behavior = $this->getBehavior();
    	$insert = $update = $delete = [];

    	foreach ($items as $item) {
    		$_entity = $this->getPost($item['url_key']);

    		if ((Import::BEHAVIOR_DELETE == $behavior || Import::BEHAVIOR_REPLACE == $behavior) && $_entity) {
    			if ($_entity) $delete[] = $_entity->getId();
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

    			if ($_entity) $new = $_entity->getData();
    			$new['ID'] = $item['post_id'];
    			$this->_importedPosts[] = $new;
    		}
    	}

    	$this->_deletePosts($delete);
    	$this->_savePosts($insert, $update);
    	$this->_importCategoriesPosts();
    	$this->_importTagsPosts();
    }

	/**
	 * @return int
	 */
    protected function _getPostAuthorId($amastyAuthorId)
    {
    	$authorId = 0;
    	$importedAuthors = $this->getImportedAuthors();
    	foreach ($importedAuthors as $_author) {
    		if ($_author['ID'] == $amastyAuthorId) {
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
		$authorId   = 0;
		$identifier = $post['url_key'];
		$_post      = $this->getPost($identifier);
		$postId     = $this->_getNextPostId();
    	if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_post) {
    		$postId = $_post->getId();
    	}

    	$importedAuthors = $this->getImportedAuthors();

    	$data = [
			'post_id'          => $postId,
			'identifier'       => $identifier,
			'title'            => $post['title'],
			'content'          => $post['full_content'],
			'excerpt'          => $post['short_content'],
			'is_active'        => $post['status'],
			'author_id'        => $this->_getPostAuthorId($post['author_id']),
			'total_views'      => $post['views'],
			'og_title'         => '',
			'og_description'   => '',
			'og_img'           => '',
			'og_type'          => '',
			'image'            => $this->getImagePath($post['post_thumbnail']),
			'meta_title'       => $post['meta_title'],
			'meta_keywords'    => $post['meta_tags'],
			'meta_description' => $post['meta_description'],
			'video_link'       => '',
			'type'             => 'image',
			'publish_date'     => $post['published_at'],
			'creation_time'    => $post['created_at'],
			'update_time'      => $post['updated_at'],
			'allow_comment'    => $post['comments_enabled'],
			'page_layout'      => '2columns-right',
			'featured'         => $post['is_featured'],
			'pinned'           => 0,
			'canonical_url'    => $post['canonical_url']
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
    protected function _getAmastyCategoriesPosts()
    {
    	if (!isset($this->_cachePosts['category'])) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from(['p' => $this->getTableName('amasty_blog_posts_category')]);
    		$this->_cachePosts['category'] = $connection->fetchAll($select);
    	}
    	return $this->_cachePosts['category'];
    }

	/**
	 * @return array
	 */
    protected function _getCategoryPosts($id)
    {
		$ids           = [];
		$posts         = $this->_getAmastyCategoriesPosts();
		$importedPosts = $this->getImportedPosts();
		foreach ($posts as $row) {
			if ($row['category_id'] == $id) {
				foreach ($importedPosts as $_post) {
					if ($_post['ID'] == $row['post_id']) {
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
    		$posts = $this->_getCategoryPosts($_row['ID']);
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
    	if ($data) $connection->insertMultiple($this->getCategoryPostTable(), $data);
    }

	/**
	 * @return array
	 */
    protected function _getAmastyTagsPosts()
    {
    	if (!isset($this->_cachePosts['tag'])) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from($this->getTableName('amasty_blog_posts_tag'));
    		$this->_cachePosts['tag'] = $connection->fetchAll($select);
    	}
    	return $this->_cachePosts['tag'];
    }

	/**
	 * @return array
	 */
    protected function _getTagPosts($id)
    {
		$ids           = [];
		$posts         = $this->_getAmastyTagsPosts();
		$importedPosts = $this->getImportedPosts();
		foreach ($posts as $row) {
			if ($row['tag_id'] == $id) {
				foreach ($importedPosts as $_post) {
					if ($_post['ID'] == $row['post_id']) {
						$ids[] = $_post['post_id'];
						break;
					}
				}
			}
		}
    	return $ids;
    }

    protected function _importTagsPosts()
    {
		$delete        = [];
		$importedPosts = $this->getImportedPosts();
		$importedTags  = $this->getImportedTags();
		$data          = [];
    	foreach ($importedTags as $_row) {
    		$posts = $this->_getTagPosts($_row['ID']);
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
    	if ($data) $connection->insertMultiple($this->getTagPostTable(), $data);
    }

	/**
	 * @return array
	 */
    protected function getAmastyComments()
    {
    	if ($this->_amastyComments == NULL) {
    		$connection = $this->getConnection();
    		$select = $connection->select()
                ->from($this->getTableName('amasty_blog_comments'))->order('comment_id DESC');
    		$this->_amastyComments = $connection->fetchAll($select);
    	}
    	return $this->_amastyComments;
    }

    protected function _importComments()
    {
    	$posts    = $this->getImportedPosts();
    	$comments = $this->getAmastyComments();
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
    		if ($_post['ID'] == $comment['post_id']) {
    			$postId = $_post['post_id'];
    		}
    	}

    	if (!$postId) return;

    	switch ($comment['status']) {
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
			'parent_id'     => $comment['reply_to'],
			'content'       => $comment['message'],
			'author'        => $comment['name'],
			'author_email'  => $comment['email'],
			'customer_id'   => 0,
			'store_id'      => Store::DEFAULT_STORE_ID,
			'status'        => $status,
			'remote_ip'     => '',
			'brower'        => '',
			'creation_time' => $comment['created_at'],
			'update_time'   => $comment['updated_at'],
			'ID'            => $comment['comment_id']
    	];
    	return $data;
    }

    public function _saveComments($insert)
    {
    	if ($insert) {
    		$inserted = $insert;
    		$connection = $this->resource->getConnection();
    		$amastyComments = $this->getAmastyComments();
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