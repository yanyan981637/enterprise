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

namespace Magezon\ProductLabels\Model\Label\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
    /**
     * @var \Magezon\ProductLabels\Model\Label
     */
    protected $label;

    /**
     * Constructor
     *
     * @param \Magezon\ProductLabels\Model\Label $label
     */
    public function __construct(\Magezon\ProductLabels\Model\Label $label)
    {
        $this->label = $label;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->label->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key
            ];
        }
        return $options;
    }
}
