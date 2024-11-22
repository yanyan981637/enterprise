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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\ProductAttachments\Model\ResourceModel\Icon\Collection;

class ListAttachment extends Template
{
    protected $_template = 'Magezon_ProductAttachments::list.phtml';

    /**
     * @var Collection
     */
    protected $iconCollection;

    /**
     * ListAttachment constructor.
     * @param Context $context
     * @param Collection $iconCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Collection $iconCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->iconCollection = $iconCollection->addIsActiveFilter();
    }

    /**
     * @return Collection
     */
    public function getIconCollection()
    {
        return $this->iconCollection;
    }
}
