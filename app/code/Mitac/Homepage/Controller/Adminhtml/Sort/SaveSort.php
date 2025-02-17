<?php
namespace Mitac\Homepage\Controller\Adminhtml\Sort;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

use Mitac\Homepage\Api\BlockRepositoryInterface;

class SaveSort extends Action
{
    private $blockRepository;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        BlockRepositoryInterface $blockRepository
    ) 
    {
        $this->blockRepository = $blockRepository;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $returnJSON = [
            'flages' => false,
            'message' => '',
        ];

        $id     = $this->getRequest()->getParam('id');
        $sort   = $this->getRequest()->getParam('sort');
        $type   = $this->getRequest()->getParam('type');

        foreach($id as $keys => $values)
        {
            if ($id) 
            {
                $block = $this->blockRepository->getById($values);
                $data['banners_id'] = $values;
                $data['sort_id'] = $sort[$keys];
                $block->setData($data);
                try 
                {
                    $this->blockRepository->save($block);
                    $returnJSON['flages'] = true;
                } 
                catch (Throwable $throwable) 
                {
                    $returnJSON['flages'] = false;
                    break;
                }
            }
        }

        if ($type == 'block')
        {
            $type = 'index';
        }

        $returnUri = $type;
        if ($returnJSON['flages'])
        {
            $this->messageManager->addSuccessMessage(__('Block sort saved successfully'));
        }
        else
        {
            $this->messageManager->addErrorMessage(__("Block not saved"));
        }
        $returnJSON['message'] = $this->getUrl('homepage/'.$returnUri.'/index');
        
        echo json_encode($returnJSON);
    }
}
