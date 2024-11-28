<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 * 
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 * 
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 * 
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 * 
 * SUPPORT@PLUGIN.COMPANY
 */

namespace PluginCompany\ContactForms\Controller\Form;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use PluginCompany\ContactForms\Model\FormRepository;

class View extends \Magento\Framework\App\Action\Action
{

    private $resultPageFactory;
    private $formRepository;
    private $page;
    private $form;
    private $scopeConfig;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param FormRepository $formRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FormRepository $formRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->formRepository = $formRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this
            ->initPage()
            ->updatePageTitle()
            ->addBreadCrumbsIfEnabled()
        ;

        return $this->getPage();
    }

    public function initPage()
    {
        $this->page = $this->resultPageFactory->create();
        return $this;
    }

    public function updatePageTitle()
    {
        $this
            ->getPage()
            ->getConfig()
            ->getTitle()
            ->set($this->getPageTitle())
        ;
        $pageMainTitle = $this->_view->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(' ');
        }
        return $this;
    }

    public function getPageTitle()
    {
        return $this->getForm()->getTitle();
    }

    public function getForm()
    {
        if(!$this->form){
            $this->initForm();
        }
        return $this->form;
    }

    private function initForm()
    {
        $this->form = $this->formRepository
            ->getById($this->getFormId());
        return $this;
    }

    public function getFormId()
    {
        return $this->_request->getParam('form_id');
    }

    public function addBreadCrumbsIfEnabled()
    {
        if($this->isBreadCrumbsEnabled()){
            $this->addBreadCrumbs();
        }
        return $this;
    }

    public function addBreadCrumbs()
    {
        if (!$this->getBreadCrumbsBlock())
            return $this;

        $this->getBreadCrumbsBlock()
            ->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_url->getBaseUrl()
                ]
            )->addCrumb(
                'contactform',
                [
                    'label' => $this->getForm()->getTitle(),
                    'link' => false,
                    'readonly' => true
                ]
            );
        return $this;
    }

    public function isBreadCrumbsEnabled()
    {
        return (bool)$this->getStoreConfig('plugincompany_contactforms/form/breadcrumbs');
    }

    public function getBreadCrumbsBlock()
    {
        return $this->_view->getLayout()->getBlock('breadcrumbs');
    }

    private function getStoreConfig($value)
    {
        return $this->scopeConfig->getValue($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * @param PageFactory $resultPageFactory
     */
    public function setResultPageFactory($resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return FormRepository
     */
    public function getFormRepository()
    {
        return $this->formRepository;
    }

    /**
     * @param FormRepository $formRepository
     */
    public function setFormRepository($formRepository)
    {
        $this->formRepository = $formRepository;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }
}
