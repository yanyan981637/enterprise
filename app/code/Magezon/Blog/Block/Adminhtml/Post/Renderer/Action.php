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

namespace Magezon\Blog\Block\Adminhtml\Post\Renderer;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;
use Magento\Framework\Url;

class Action extends Text
{
    /**
     * @var Url
     */
	protected $_urlBuilder;

    /**
     * @param Context $context
     * @param Url $urlBuilder
     */
	public function __construct(
        Context $context,
        Url $urlBuilder
    ) {
		$this->_urlBuilder = $urlBuilder;
        parent::__construct($context);
	}

	public function _getValue(DataObject $row){
		$editUrl = $this->_urlBuilder->getUrl(
            'blog/post/edit',
            [
                'post_id' => $row['post_id']
            ]
        );
		return sprintf("<a target='_blank' href='%s'>Edit</a>", $editUrl);
	}
}