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

namespace Magezon\ProductAttachments\Plugin\Model\Order\Email\Sender;

use Magento\Framework\Registry;

class OrderSender
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function beforeSend(
        $subject,
        $order
    ) {
        $this->registry->unregister('mgzatm_type');
        $this->registry->unregister('mgzatm_source');
        $this->registry->register('mgzatm_type', 'order');
        $this->registry->register('mgzatm_source', $order);
    }
}
