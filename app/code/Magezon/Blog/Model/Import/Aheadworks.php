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

class Aheadworks extends AbstractImport
{
	/**
	 * @var string
	 */
	protected $_platform = 'magento';

	/**
	 * @var array
	 */
	protected $_mpPosts;

	/**
	 * @var array
	 */
	protected $_mpCategories;

	/**
	 * @var array
	 */
	protected $_mpTags;

	/**
	 * @var array
	 */
	protected $_mpAuthors;

	/**
	 * @var array
	 */
	protected $_mpComments;

	/**
	 * @var array
	 */
	protected $_cachePosts = [];

	/**
	 * @return array
	 */
	protected function getMPCategories()
	{
		if ($this->_mpCategories == NULL) {
			$connection = $this->getConnection();
			$select = $connection->select()->from($this->getTableName('aw_blog_category'));
			$this->_mpCategories = $connection->fetchAll($select);
		}
		return $this->_mpCategories;
	}

	protected function _importCategories()
	{
		$items    = $this->getMPCategories();
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
    			$new['ID'] = $item['id'];
    			$this->_importedCategories[] = $new;
			}
		}
		$this->_deleteCategories($delete);
		$this->_saveCategories($insert, $update);
	}

    /**
     * @param array $delete
     * @return $this
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
    		$mpCategories = $this->getMPCategories();
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
			'parent_id'        => 0,
			'include_in_menu'  => 1,
			'position'         => 0,
			'meta_title'       => $category['meta_title'],
			'meta_keywords'    => '',
			'meta_description' => $category['meta_description'],
			'update_time'      => $category['created_at'],
			'creation_time'    => $category['updated_at'],
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
    protected function getMPTags()
    {
    	if ($this->_mpTags == NULL) {
    		$connection = $this->getConnection();
			$select = $connection->select()->from($this->getTableName('aw_blog_tag')); 
    		$this->_mpTags = $connection->fetchAll($select);
    	}
    	return $this->_mpTags;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function _importTags()
    {
		$items = $this->getMPTags();
		$behavior = $this->getBehavior();
		$insert = $update = $delete = []; 

    	foreach ($items as $item) {
    		$_entity = $this->getTag($item['name']);

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
    			$new['ID'] = $item['id'];
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
    	$_tag  = $this->getTag($tag['name']);
    	$tagId = $this->_getNextTagId();
    	if (Import::BEHAVIOR_APPEND == $this->getBehavior() && $_tag) {
    		$tagId = $_tag->getId();
    	}
    	$data = [
			'tag_id'           => $tagId,
			'identifier'       => $tag['name'],
			'title'            => $tag['name'],
			'content'          => '',
			'is_active'        => 1,
			'meta_title'       => '',
			'meta_keywords'    => '',
			'meta_description' => '',
			'creation_time'    => $tag['created_at'],
			'update_time'      => $tag['updated_at'],
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
    protected function getMPPosts()
    {
    	if ($this->_mpPosts == NULL) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from($this->getTableName('aw_blog_post'));
    		$this->_mpPosts = $connection->fetchAll($select);
    	}
    	return $this->_mpPosts;
    }

    protected function _importPosts()
    {
    	$items = $this->getMPPosts();
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
    			$new['ID'] = $item['id'];
    			$this->_importedPosts[] = $new;
    		}
    	}

    	$this->_deletePosts($delete);
    	$this->_savePosts($insert, $update);
    	$this->_importCategoriesPosts();
    	$this->_importTagsPosts();
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
			'content'          => $post['content'],
			'excerpt'          => $post['short_content'],
			'is_active'        => $post['status']!=='draft' ? 1 : 0,
			'author_id'        => 0,
			'total_views'      => 0,
			'og_title'         => '',
			'og_description'   => '',
			'og_img'           => '',
			'og_type'          => '',
			'image'            => $this->getImagePath($post['featured_image_file']),
			'meta_title'       => $post['meta_title'],
			'meta_keywords'    => '',
			'meta_description' => $post['meta_description'],
			'video_link'       => '',
			'type'             => 'image',
			'publish_date'     => $post['publish_date'],
			'creation_time'    => $post['created_at'],
			'update_time'      => $post['updated_at'],
			'allow_comment'    => $post['is_allow_comments'],
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
    protected function _getMPCategoriesPosts()
    {
    	if (!isset($this->_cachePosts['category'])) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from(['p' => $this->getTableName('aw_blog_post_category')]);
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
		$posts         = $this->_getMPCategoriesPosts();
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
    protected function _getMPTagsPosts()
    {
    	if (!isset($this->_cachePosts['tag'])) {
    		$connection = $this->getConnection();
    		$select = $connection->select()->from($this->getTableName('aw_blog_post_tag'));
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
		$posts         = $this->_getMPTagsPosts();
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

    protected function _importComments()
    {
    	
    }
}