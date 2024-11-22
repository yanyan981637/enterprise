<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Block\Adminhtml;

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
					'title'    => __('Add New Label'),
					'link'     => $this->getUrl('productlabels/label/new'),
					'resource' => 'Magezon_ProductLabels::label_save'
				],
				[
					'title'    => __('Manage Labels'),
					'link'     => $this->getUrl('productlabels/label'),
					'resource' => 'Magezon_ProductLabels::label'
				],
				[
					'title'    => __('Settings'),
					'link'     => $this->getUrl('adminhtml/system_config/edit/section/productlabels'),
					'resource' => 'Magezon_ProductLabels::settings'
				]
			],
			[
				'class' => 'separator'
			],
			[
				'title'  => __('User Guide'),
				'link'   => $this->getSupportLink(),
				'target' => '_blank'
			],
			[
				'title'  => __('Change Log'),
				'link'   => $this->getSupportLink(),
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

	public function getSupportLink()
	{
		return 'https://magezon.com/product-labels.html';
	}
}
