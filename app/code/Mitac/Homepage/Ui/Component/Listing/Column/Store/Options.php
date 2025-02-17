<?php
namespace Mitac\Homepage\Ui\Component\Listing\Column\Store;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options[] = [
                                'value' => "1",
                                'label' => "Australia Store View",
                                '__disableTmpl' => true
                            ];
        $this->options[] = [
                                'value' => "2",
                                'label' => "New Zealand Store View",
                                '__disableTmpl' => true
                            ];
        $this->options[] = [
                                'value' => "5",
                                'label' => "Magellan Australia Store View",
                                '__disableTmpl' => true
                            ];
        $this->options[] = [
                                'value' => "6",
                                'label' => "Magellan New Zealand Store View",
                                '__disableTmpl' => true
                            ];
        
        return $this->options;
    }
}
