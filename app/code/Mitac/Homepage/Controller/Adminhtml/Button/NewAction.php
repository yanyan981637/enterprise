<?php
namespace Mitac\Homepage\Controller\Adminhtml\Button;

use Magento\Cms\Controller\Adminhtml\Block;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Block
{
    const ADMIN_RESOURCE = 'Mitac_Homepage::button_view';

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mitac_Homepage::button_view');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
