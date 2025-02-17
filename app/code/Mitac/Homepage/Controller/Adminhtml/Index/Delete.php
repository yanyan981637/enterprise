<?php
namespace Mitac\Homepage\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

use Mitac\Homepage\Api\BlockRepositoryInterface;
use Mitac\Homepage\Helper\StoreData;

class Delete extends Action
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var StoreData
     */
    protected $StroeHelper;

    /**
     * @param Action\Context $context
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        Action\Context $context,
        BlockRepositoryInterface $blockRepository,
        StoreData $storehelper
    )
    {
        parent::__construct($context);
        $this->blockRepository = $blockRepository;
        $this->StroeHelper = $storehelper;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) 
        {
            $block = $this->blockRepository->getById($id);
            if ($block->getType() == 'block') 
            {
                $returnUri = 'index';
            } 
            else 
            {
                $returnUri = $block->getType();
            }

            try 
            {
                $this->blockRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The block has been deleted.'));

                $this->StroeHelper->deleteBannerStore($id);
                $this->StroeHelper->deletePageIdenifier($id);

                return $resultRedirect->setPath('*/'.$returnUri.'/');
            }
            catch (\Exception $e) 
            {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/'.$returnUri.'/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a block to delete.'));

        return $resultRedirect->setPath('*/'.$returnUri.'/');
    }
}
