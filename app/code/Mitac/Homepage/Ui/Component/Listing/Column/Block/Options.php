<?php
namespace Mitac\Homepage\Ui\Component\Listing\Column\Block;

use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;
/**
 * Store Options for Cms Pages and Blocks
 */
class Options extends StoreOptions
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = 0;

        $this->generateCurrentOptions();
        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
