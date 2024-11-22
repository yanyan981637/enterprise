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
use Magezon\Core\Model\Source\IsActive;

class Status extends Text
{
    /**
     * @var IsActive
     */
	protected $_isActive;

    /**
     * @param Context $context
     * @param IsActive $isActive
     */
	public function __construct(
		Context $context,
		IsActive $isActive
    ) {
        parent::__construct($context);
		$this->_isActive = $isActive;
	}

    /**
     * @param DataObject $row
     * @return string
     */
	public function _getValue(DataObject $row)
	{
		$status = $row->getIsActive();
		$availableOptions = $this->_isActive->toOptionArray();

		$statusLabel = '';
		foreach ($availableOptions as $k => $_status) {
            if ($_status['value'] == $status) {
                $statusLabel = '<span class="mgz-status mgz-status_' . $_status['value'] .  '">' . $_status['label'] .
                    '</span>';
                break;
            }
        }
        return $statusLabel;
	}
}