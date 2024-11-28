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

class MageFan extends AbstractImport
{
	/**
	 * @var string
	 */
	protected $_platform = 'magento';

	/**
	 * @var array
	 */
	protected $_mfPosts;

	/**
	 * @var array
	 */
	protected $_mfCategories;

	/**
	 * @var array
	 */
	protected $_mfTags;

	/**
	 * @var array
	 */
	protected $_mfAuthors;

	/**
	 * @var array
	 */
	protected $_mfComments;

	/**
	 * @var array
	 */
	protected $_cachePosts = [];

	/**
	 * @return array
	 */
	protected function getMFCategories()
	{
		if ($this->_mfCategories == NULL) {
			$connection = $this->getConnection();
			$select = $connection->select()->from($this->getTableName('magefan_blog_category'));
			$this->_mfCategories = $connection->fetchAll($select);
		}
		return $this->_mfCategories;
	}

	protected function _importCategories()
	{
		$items    = $this->getMFCategories();
		$behavior = $this->getBehavior();
		$insert   = $update = $delete = [];

		foreach ($items as $item) {
			$_entity = $this->getCategory($item['identifier']);

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
    		$mfCategories = $this->getMFCategories();
    		foreach ($insert as &$row) {
    			$parentId = 0;
    			foreach ($mfCategories as $_row) {
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
    	$_cat = $this->getCategory($category['identifier']);
    	$categoryId = $this->_getNextCategoryId();
    	if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_cat) {
    		$categoryId = $_cat->getId();
    	}
    	$data = [
			'category_id'      => $categoryId,
			'identifier'       => $category['identifier'],
			'title'            => $category['title'],
			'content'          => $category['content'],
			'is_active'        => $category['is_active'],
			'parent_id'        => '',
			'include_in_menu'  => $category['include_in_menu'],
			'position'         => $category['position'],
			'meta_title'       => $category['meta_title'],
			'meta_keywords'    => $category['meta_keywords'],
			'meta_description' => $category['meta_description'],
			'list_layout'      => 'fixed_thumb',
			'page_layout'      => $category['page_layout'],
			'grid_col'         => 3,
			'canonical_url'    => ''
    	];
    	return $data;
    }

	/**
	 * @return array
	 */
    protected function getMFTags()
    {
    	if ($this->_mfTags == NULL) {
    		$connection = $this->getConnection();
			$select = $connection->select()->from($this->getTableName('magefan_blog_tag')); 
    		$this->_mfTags = $connection->fetchAll($select);
    	}
    	return $this->_mfTags;
    }

    protected function _importTags()
    {
		$items = $this->getMFTags();
		$behavior = $this->getBehavior();
		$insert = $update = $delete = [];

    	foreach ($items as $item) {
    		$_entity = $this->getTag($item['identifier']);

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
    	$_tag  = $this->getTag($tag['identifier']);
    	$tagId = $this->_getNextTagId();
    	if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_tag) {
    		$tagId = $_tag->getId();
    	}
    	$data = [
			'tag_id'           => $tagId,
			'identifier'       => $tag['identifier'],
			'title'            => $tag['title'],
			'content'          => $tag['content'],
			'is_active'        => $tag['is_active'],
			'meta_title'       => $tag['meta_title'],
			'meta_keywords'    => $tag['meta_keywords'],
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

    protected function _importAuthors()
    {
    }

	/**
	 * @return array
	 */
    protected function getMFPosts()
    {
    	if ($this->_mfPosts == NULL) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from($this->getTableName('magefan_blog_post'));
    		$this->_mfPosts = $connection->fetchAll($select);
    	}
    	return $this->_mfPosts;
    }

    protected function _importPosts()
    {
    	$items = $this->getMFPosts();
    	$behavior = $this->getBehavior();
    	$insert = $update = $delete = [];

    	foreach ($items as $item) {
    		$_entity = $this->getPost($item['identifier']);

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
    protected function _getPostAuthorId($mfAuthorId)
    {
    	$authorId = 0;
    	$importedAuthors = $this->getImportedAuthors();
    	foreach ($importedAuthors as $_author) {
    		if ($_author['ID'] == $mfAuthorId) {
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
		$identifier = $post['identifier'];
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
			'content'          => $post['content'],
			'excerpt'          => $post['short_content'],
			'is_active'        => $post['is_active'],
			'author_id'        => 0,
			'total_views'      => 0,
			'og_title'         => $post['og_title'],
			'og_description'   => $post['og_description'],
			'og_img'           => $post['og_img'],
			'og_type'          => $post['og_type'],
			'image'            => $this->getImagePath($post['featured_img']),
			'meta_title'       => $post['meta_title'],
			'meta_keywords'    => $post['meta_keywords'],
			'meta_description' => $post['meta_description'],
			'video_link'       => '',
			'type'             => 'image',
			'publish_date'     => $post['publish_time'],
			'creation_time'    => $post['creation_time'],
			'update_time'      => $post['update_time'],
			'allow_comment'    => 1,
			'page_layout'      => $post['page_layout'],
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
    protected function _getMFCategoriesPosts()
    {
    	if (!isset($this->_cachePosts['category'])) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from($this->getTableName('magefan_blog_post_category'));
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
		$posts         = $this->_getMFCategoriesPosts();
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
    protected function _getMFTagsPosts()
    {
    	if (!isset($this->_cachePosts['tag'])) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from(['p' => $this->getTableName('magefan_blog_post_tag')]);
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
		$posts         = $this->_getMFTagsPosts();
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
    protected function getMFComments()
    {
    	if ($this->_mfComments == NULL) {
    		$connection = $this->getConnection();
    		$select = $connection->select()
                ->from($this->getTableName('magefan_blog_comment'))->order('comment_id DESC');
    		$this->_mfComments = $connection->fetchAll($select);
    	}
    	return $this->_mfComments;
    }

    protected function _importComments()
    {
    	$posts    = $this->getImportedPosts();
    	$comments = $this->getMFComments();
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
			'parent_id'     => $comment['parent_id'],
			'content'       => $comment['text'],
			'author'        => $comment['author_nickname'],
			'author_email'  => $comment['author_email'],
			'customer_id'   => 0,
			'store_id'      => Store::DEFAULT_STORE_ID,
			'status'        => $status,
			'remote_ip'     => '',
			'brower'        => '',
			'creation_time' => $comment['creation_time'],
			'update_time'   => $comment['update_time'],
			'ID'            => $comment['comment_id']
    	];
    	return $data;
    }

    public function _saveComments($insert)
    {
    	if ($insert) {
    		$inserted = $insert;
    		$connection = $this->resource->getConnection();
    		$mfComments = $this->getMFComments();
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