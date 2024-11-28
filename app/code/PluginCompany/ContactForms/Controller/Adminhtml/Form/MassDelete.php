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
namespace PluginCompany\ContactForms\Controller\Adminhtml\Form;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use PluginCompany\ContactForms\Model\FormRepository;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'PluginCompany_ContactForms::manage_form';

    private $formRepository;
    private $deleted = 0;
    private $errors = 0;

    /**
     * @param Context $context
     * @param FormRepository $formRepository
     */
    public function __construct(
        Context $context,
        FormRepository $formRepository
    )
    {
        $this->formRepository = $formRepository;
        parent::__construct($context);
    }

    /**
     * @return FormRepository
     */
    public function getFormRepository()
    {
        return $this->formRepository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this
            ->deleteFormsBasedOnPostData()
            ->addResultMessages()
        ;
        return $this->redirectToListing();
    }

    private function deleteFormsBasedOnPostData()
    {
        $ids = $this->getSelectedFormIds();
        foreach ($ids as $id) {
            $this->tryDeleteForm($id);
        }
        return $this;
    }

    private function getSelectedFormIds()
    {
        $ids = $this->getRequest()->getPost('selected', []);
        if(empty($ids) && !$this->hasFormFilter()){
            $ids = $this->getAllFormIds();
        };
        return $ids;
    }

    private function hasFormFilter()
    {
        $excluded = $this->getRequest()->getParam('excluded');
        if($excluded == "false"){
            return false;
        }
        return true;
    }

    private function getAllFormIds()
    {
        return $this->formRepository->getAllIds();
    }

    private function tryDeleteForm($id)
    {
        try{
            $this->getFormRepository()->deleteById($id);
            $this->deleted++;
        }
        catch(\Exception $e) {
            $this->errors++;
        }
        return $this;
    }

    private function addResultMessages()
    {
        $this
            ->addSuccessMessage()
            ->addErrorMessage()
        ;
        return $this;
    }

    private function addSuccessMessage()
    {
        if(!$this->deleted) return $this;
        $this->messageManager
            ->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.',
                    $this->deleted)
            );
        return $this;
    }

    private function addErrorMessage()
    {
        if(!$this->errors) return $this;
        $this->messageManager
            ->addErrorMessage(
                __('An error occured while deleting %1 record(s).',
                    $this->errors)
            );
        return $this;
    }

    private function redirectToListing()
    {
        return $this->createRedirect()
            ->setPath('*/*/');
    }

    private function createRedirect()
    {
        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
    }
}