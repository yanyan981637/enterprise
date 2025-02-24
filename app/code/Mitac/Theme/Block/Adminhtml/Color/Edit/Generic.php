<?php

namespace Mitac\Theme\Block\Adminhtml\Color\Edit;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Generic implements ButtonProviderInterface
{

    /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    public function __construct(
        Context $context,
        AuthorizationInterface $authorization
    ){
        $this->context = $context;
        $this->_authorization = $authorization;
    }

    public function getButtonData(): array
    {
        return [];
    }


    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function getColorId()
    {
        return $this->context->getRequestParam('color_id');
    }

}
