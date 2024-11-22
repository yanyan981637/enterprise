<?php
namespace PluginCompany\ContactForms\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PluginCompany\ContactForms\Model\FormFactory;

/**
 * Contact Form Controller Router
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var ResponseInterface
     */
    protected $_response;

    private $identifier;
    private $scopeConfig;
    private $formFactory;

    /**
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param UrlInterface $url
     * @param FormFactory $formFactory,
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param ScopeConfigInterface $configScopeConfigInterface
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        UrlInterface $url,
        StoreManagerInterface $storeManager,
        ResponseInterface $response,
        FormFactory $formFactory,
        ScopeConfigInterface $configScopeConfigInterface
    )
    {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
        $this->formFactory = $formFactory;
        $this->scopeConfig = $configScopeConfigInterface;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        $this->identifier = explode('/',trim($request->getPathInfo(), '/'));

        if(!$this->isCorrectPrefixUsed()){
            return null;
        }
        if(!$this->isPathDepthCorrect())
        {
            return null;
        }

        $condition = new DataObject(['identifier' => $this->identifier, 'continue' => true]);
        $this->_eventManager->dispatch(
            'plugincompany_contactforms_controller_router_match_before',
            ['router' => $this, 'condition' => $condition]
        );
        $this->identifier = $condition->getIdentifier();

        if ($condition->getRedirectUrl()) {
            $this->_response->setRedirect($condition->getRedirectUrl());
            $request->setDispatched(true);
            return $this->actionFactory->create('Magento\Framework\App\Action\Redirect');
        }

        if (!$condition->getContinue()) {
            return null;
        }


        $form = $this->formFactory->create();
        $formId = $form
            ->getResource()
            ->getFormIdForFrontEndPageUrlKey($this->getUrlKeyFromIdentifier(), $this->getStoreId())
        ;

        if(!$formId){
            return null;
        }

        $request
            ->setModuleName('contactforms')
            ->setControllerName('form')
            ->setActionName('view')
            ->setParam('form_id', $formId)
        ;

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

    public function isCorrectPrefixUsed()
    {
        if($this->usesPrefix() && $this->identifier[0] != $this->getPrefix())
        {
            return false;
        }
        return true;
    }

    public function usesPrefix()
    {
        return (bool)$this->getPrefix();
    }

    public function getPrefix()
    {
        return $this->getStoreConfig('plugincompany_contactforms/form/url_prefix');
    }

    public function getStoreConfig($value)
    {
        return $this->scopeConfig->getValue($value, ScopeInterface::SCOPE_STORE);
    }

    public function isPathDepthCorrect()
    {
        return $this->getPathDepth() === count($this->identifier);
    }

    public function getPathDepth()
    {
        $depth = 1;
        if($this->usesPrefix()) {
            $depth = 2;
        }
        return $depth;
    }

    public function getUrlKeyFromIdentifier()
    {
        return str_replace($this->getSuffix(), '', $this->getLastPathPart());
    }

    public function getLastPathPart()
    {
        return $this->identifier[$this->getPathDepth() - 1];
    }

    public function getSuffix()
    {
        return $this->getStoreConfig('plugincompany_contactforms/form/url_suffix');
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
