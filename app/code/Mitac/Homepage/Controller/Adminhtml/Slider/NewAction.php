<?php
namespace Mitac\Homepage\Controller\Adminhtml\Slider;

use Magento\Cms\Controller\Adminhtml\Block;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Block
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
