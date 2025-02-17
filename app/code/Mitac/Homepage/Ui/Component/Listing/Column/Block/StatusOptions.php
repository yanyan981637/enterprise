<?php
namespace Mitac\Homepage\Ui\Component\Listing\Column\Block;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
/**
 * Store Options for Cms Pages and Blocks
 */
class StatusOptions implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */

    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;
    
    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->currentOptions['Enabled']['label'] = __('Enabled');
        $this->currentOptions['Enabled']['value'] = '1';
        $this->currentOptions['Enabled']['__disableTmpl'] = true;
        $this->currentOptions['Disabled']['label'] = __('Disabled');
        $this->currentOptions['Disabled']['value'] = '0';
        $this->currentOptions['Disabled']['__disableTmpl'] = true;
        
        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
