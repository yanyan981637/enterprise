<?php 
namespace Mitac\Theme\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ThemeColor extends AbstractSource {
  public function getAllOptions()
    {
      if (!$this->_options) {
        $this->_options = [
          ['label' => __('orange'), 'value' => 'orange'],
          ['label' => __('green'), 'value' => 'green'],
      ];
      }
        return $this->_options;
    }

    public function toOptionArray()
    {
      return [
        ['label' => __('orange'), 'value' => 'orange'],
        ['label' => __('green'), 'value' => 'green'],
    ];
    }
}