<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Customer\Block\Adminhtml\Edit;

class GenericButton
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public function afterGetButtonData($subject, $result)
    {
        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::delete_customers')) {
            if ($subject instanceof \Magento\Customer\Block\Adminhtml\Edit\DeleteButton) {
                $result = [];
            }
        }

        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::save_customers')) {
            if ($subject instanceof \Magento\Customer\Block\Adminhtml\Edit\SaveAndContinueButton
                || $subject instanceof \Magento\Customer\Block\Adminhtml\Edit\SaveButton
            ) {
                $result = [];
            }
        }

        return $result;
    }
}
