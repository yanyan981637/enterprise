<?php
namespace Mitac\Homepage\Controller\Adminhtml\Sort;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class getItems extends Action
{

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $store_id = $this->getRequest()->getParam('storeid');
        $blocktype = $this->getRequest()->getParam('type');

        $returnJSON = [
            'flages' => false,
            'error' => '',
            'items' => ''
        ];

        if ($store_id > 0 && $blocktype != '')
        {
            $response = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $layout = $response->addHandle('homepage_sort_sort')->getLayout();

            $block = $layout->getBlock('menuTree');
            $returnJSON['items'] = $block->getSortItemsHTML($store_id, $blocktype);
            if ($returnJSON['items'] != '')
            {
                $returnJSON['flages'] = true;
            }
            else
            {
                $returnJSON['error'] = 'The Store View not Item.';
            }
        }
        else
        {
            $returnJSON['error'] = 'Please select Store View.';
        }
        
        echo json_encode($returnJSON);
    }
}
