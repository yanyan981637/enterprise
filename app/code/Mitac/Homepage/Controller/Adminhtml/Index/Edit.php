<?php
namespace Mitac\Homepage\Controller\Adminhtml\Index;

use Mitac\Homepage\Controller\Adminhtml\PageBlock;

class Edit extends PageBlock
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $manageEntity = __('Blocks');
        $id = $this->getRequest()->getParam('id');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mitac_Homepage::block_gird')->addBreadcrumb($manageEntity, $manageEntity);

        if ($id === null) 
        {
            $resultPage->addBreadcrumb(__('New Block'), __('New Block'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Block'));
        } 
        else
        {
            $resultPage->addBreadcrumb(__('Edit Block'), __('Edit Block'));
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Block'));
        }
        
        return $resultPage;
    }
}
