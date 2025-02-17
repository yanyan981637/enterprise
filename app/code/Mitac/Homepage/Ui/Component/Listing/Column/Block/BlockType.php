<?php
namespace Mitac\Homepage\Ui\Component\Listing\Column\Block;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
/**
 * Store Options for Cms Pages and Blocks
 */
class BlockType implements OptionSourceInterface
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

        $this->currentOptions['Select Block Type']['label'] = __('Select Block Type');
        $this->currentOptions['Select Block Type']['value'] = '';
        $this->currentOptions['Block']['label'] = __('Block');
        $this->currentOptions['Block']['value'] = 'block';
        $this->currentOptions['Block']['__disableTmpl'] = true;
        $this->currentOptions['Button']['label'] = __('Button');
        $this->currentOptions['Button']['value'] = 'button';
        $this->currentOptions['Button']['__disableTmpl'] = true;
        $this->currentOptions['Slider']['label'] = __('Slider');
        $this->currentOptions['Slider']['value'] = 'slider';
        $this->currentOptions['Slider']['__disableTmpl'] = true;
        $this->currentOptions['Banner']['label'] = __('Banner');
        $this->currentOptions['Banner']['value'] = 'banner';
        $this->currentOptions['Banner']['__disableTmpl'] = true;

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
