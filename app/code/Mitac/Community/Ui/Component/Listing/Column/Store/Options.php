<?php
namespace Mitac\Community\Ui\Component\Listing\Column\Store;

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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $repository = $objectManager->get('\Magento\Store\Api\StoreRepositoryInterface');

        $this->options[] = [
                            'value' => ',0,',
                            'label' => 'All Store Views',
                            '__disableTmpl' => true
                        ];

        $stores = $repository->getList();
        foreach ($stores as $store) 
        {
            if($store->getName()!='Admin')
            {
                $this->options[] = [
                                        'value' => ','.$store->getId().',',
                                        'label' => $store->getName(),
                                        '__disableTmpl' => true
                                    ];
            }
        }

        return $this->options;
    }
}
