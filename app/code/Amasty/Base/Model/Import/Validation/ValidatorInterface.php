<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Import\Validation;

/**
 * @since 1.4.6
 */
interface ValidatorInterface
{
    /**
     * Return array with error codes. Return true on validation pass
     *
     * @param array  $rowData
     * @param string $behavior
     *
     * @throws \Amasty\Base\Exceptions\StopValidation
     * @return array|bool
     */
    public function validateRow(array $rowData, $behavior);

    /**
     * Return array: error_code => error_message
     *
     * @return array
     */
    public function getErrorMessages();

    /**
     * @param string $message
     * @param int $level
     *
     * @return ValidatorInterface
     */
    public function addRuntimeError($message, $level);
}
