<?php
namespace Mitac\Homepage\Controller\Adminhtml\Sort;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Api\BlockRepositoryInterface;
use Mitac\Homepage\Model\BlockFactory;

class Sort extends Action
{
    private $blockRepository;
    private $blockFactory;
    protected $resultPageFactory;

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
        $blockType = $this->getRequest()->getParam('type');
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $block = $page->getLayout()->getBlock('menuTree');
        $block->setData('blockType', $blockType);

        $this->_initAction();
        if ($blockType == 'block')
        {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Sort Block'));
        } 
        else 
        {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Sort '.$blockType.' Block'));
        }
        
        $breadcrumb = __('Sort Block');
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
