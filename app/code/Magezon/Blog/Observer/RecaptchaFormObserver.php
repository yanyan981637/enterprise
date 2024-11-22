<?php

namespace Magezon\Blog\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\UrlInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\RequestHandlerInterface;
use Magezon\Blog\Helper\Data;

class RecaptchaFormObserver implements ObserverInterface
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var IsCaptchaEnabledInterface
     */
    protected $isCaptchaEnabled;

    /**
     * @var RequestHandlerInterface
     */
    protected $requestHandler;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param UrlInterface $url
     * @param IsCaptchaEnabledInterface $isCaptchaEnabled
     * @param RequestHandlerInterface $requestHandler
     * @param RedirectInterface $redirect
     * @param Data $helperData
     */
    public function __construct(
        UrlInterface $url,
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        RequestHandlerInterface $requestHandler,
        RedirectInterface $redirect,
        Data $helperData
    )
    {
        $this->url = $url;
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->requestHandler = $requestHandler;
        $this->redirect = $redirect;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws InputException
     */
    public function execute(Observer $observer)
    {
        $key = 'blog_comment_form';
        if ($this->helperData->getRecaptchaCommentForm()) {
            /** @var Action $controller */
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $response = $controller->getResponse();
            $redirectOnFailureUrl = $this->redirect->getRedirectUrl();
            $this->requestHandler->execute($key, $request, $response, $redirectOnFailureUrl);
        }
    }
}
