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

namespace Magezon\Blog\Block\Adminhtml;

class TopMenu extends \Magezon\Core\Block\Adminhtml\TopMenu
{
	/**
	 * Init menu items
	 * 
	 * @return array
	 */
	public function intLinks()
	{
		$links = [
			[
				[
					'title'    => __('Add New Post'),
					'link'     => $this->getUrl('blog/post/new'),
					'resource' => 'Magezon_Blog::post_save'
				],
				[
					'title'    => __('Manage Posts'),
					'link'     => $this->getUrl('blog/post'),
					'resource' => 'Magezon_Blog::post'
				]
			],
			[
				[
					'title'    => __('Add New Category'),
					'link'     => $this->getUrl('blog/category/new'),
					'resource' => 'Magezon_Blog::category_save'
				],
				[
					'title'    => __('Manage Categories'),
					'link'     => $this->getUrl('blog/category'),
					'resource' => 'Magezon_Blog::category'
				]
			],
			[
				[
					'title'    => __('Add New Tag'),
					'link'     => $this->getUrl('blog/tag/new'),
					'resource' => 'Magezon_Blog::tag_save'
				],
				[
					'title'    => __('Manage Tags'),
					'link'     => $this->getUrl('blog/tag'),
					'resource' => 'Magezon_Blog::tag'
				]
			],
			[
				[
					'title'    => __('Add New Author'),
					'link'     => $this->getUrl('blog/author/new'),
					'resource' => 'Magezon_Blog::author_save'
				],
				[
					'title'    => __('Manage Authors'),
					'link'     => $this->getUrl('blog/author'),
					'resource' => 'Magezon_Blog::author'
				]
			],
			[
				[
					'title'    => __('Manage Comments'),
					'link'     => $this->getUrl('blog/comment'),
					'resource' => 'Magezon_Blog::comment'
				],
				[
					'title'    => __('My Profile'),
					'link'     => $this->getUrl('blog/profile'),
					'resource' => 'Magezon_Blog::profile'
				],
				[
					'title'    => __('Import'),
					'link'     => $this->getUrl('blog/import'),
					'resource' => 'Magezon_Blog::import'
				],
				[
					'title'    => __('Settings'),
					'link'     => $this->getUrl('adminhtml/system_config/edit/section/mgzblog'),
					'resource' => 'Magezon_Blog::settings'
				]
			],
			[
				'class' => 'separator'
			],
			[
				'title'  => __('User Guide'),
				'link'   => 'https://magezon.com/pub/media/productfile/blog-v1.0.0-installation-guides.pdf',
				'target' => '_blank'
			],
			[
				'title'  => __('Change Log'),
				'link'   => 'https://www.magezon.com/magento-2-blog-extension.html#release_notes',
				'target' => '_blank'
			],
			[
				'title'  => __('Get Support'),
				'link'   => $this->getSupportLink(),
				'target' => '_blank'
			]
		];
		return $links;
	}
}