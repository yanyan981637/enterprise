<?php
namespace Mitac\SystemAPI\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Apitype implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'CAS', 'label' => __('CAS (Java)')],
            ['value' => 'ID4', 'label' => __('ID4 (DotNet Core)')],
        ];
    }
}
