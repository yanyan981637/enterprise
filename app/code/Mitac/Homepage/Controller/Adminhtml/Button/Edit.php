<?php
namespace Mitac\Homepage\Controller\Adminhtml\Button;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Api\BlockRepositoryInterface;
use Mitac\Homepage\Model\BlockFactory;

class Edit extends Action
{
    private $blockRepository;
    private $blockFactory;

    /**
     * @param Context $context
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        Context $context,
        BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory
    )
    {
        parent::__construct($context);
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) 
        {
            try 
            {
                /** @var BlockInterface $model */
                $model = $this->blockRepository->getById($id);
            } 
            catch (Exception $exception) 
            {
                $this->messageManager->addErrorMessage(__('This button block no longer exists.'));
                $this->_redirect('block/*');

                return;
            }
        } 
        else 
        {
            $model = $this->blockFactory->create();
        }

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Blocks'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? __("Edit button Block '%1'", $model->getId()) : __('New button Block')
        );

        $breadcrumb = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mitac_Block::block');

        return $this;
    }
}
