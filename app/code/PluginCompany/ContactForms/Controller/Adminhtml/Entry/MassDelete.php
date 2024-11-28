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
namespace PluginCompany\ContactForms\Controller\Adminhtml\Entry;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use PluginCompany\ContactForms\Model\EntryRepository;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'PluginCompany_ContactForms::manage_entry';

    private $entryRepository;
    private $deleted = 0;
    private $errors = 0;

    /**
     * @param Context $context
     * @param EntryRepository $entryRepository
     */
    public function __construct(
        Context $context,
        EntryRepository $entryRepository
    )
    {
        $this->entryRepository = $entryRepository;
        parent::__construct($context);
    }

    /**
     * @return EntryRepository
     */
    public function getEntryRepository()
    {
        return $this->entryRepository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this
            ->deleteEntrysBasedOnPostData()
            ->addResultMessages()
        ;
        return $this->redirectToReferringPage();
    }

    private function deleteEntrysBasedOnPostData()
    {
        $ids = $this->getSelectedEntryIds();
        foreach ($ids as $id) {
            $this->tryDeleteEntry($id);
        }
        return $this;
    }

    private function getSelectedEntryIds()
    {
        $ids = $this->getRequest()->getPost('selected', []);
        if(empty($ids) && !$this->hasEntryFilter()){
            $ids = $this->getAllEntryIds();
        };
        return $ids;
    }

    private function hasEntryFilter()
    {
        $excluded = $this->getRequest()->getParam('excluded');
        if($excluded == "false"){
            return false;
        }
        return true;
    }

    private function getAllEntryIds()
    {
        if($this->getFormId()) {
            return $this->entryRepository->getAllIdsByFormId($this->getFormId());
        }
        return $this->entryRepository->getAllIds();
    }

    private function getFormId()
    {
        return $this->getRequest()->getParam('form_id');
    }

    private function tryDeleteEntry($id)
    {
        try{
            $this->getEntryRepository()->deleteById($id);
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

    private function redirectToReferringPage()
    {
        if($this->getFormId()) {
            return $this->createRedirect()
                ->setPath(
                    'plugincompany_contactforms/form/edit',
                    [
                        'form_id' => $this->getFormId()
                    ]
                );
        }
        return $this->createRedirect()
            ->setPath('*/*/');
    }

    private function createRedirect()
    {
        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
    }
}