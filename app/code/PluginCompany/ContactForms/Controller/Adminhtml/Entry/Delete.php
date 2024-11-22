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

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use PluginCompany\ContactForms\Controller\Adminhtml\Entry;

class Delete extends Entry
{

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if (!$this->getEntryId()) {
            $this->messageManager->addErrorMessage(__('We can\'t find a Entry to delete.'));
            return $this->redirectToReferrer();
        }
        try {
            $this->entryRepository->deleteById($this->getEntryId());
            $this->messageManager->addSuccessMessage(__('You deleted the Entry.'));
            return $this->redirectToReferrer();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->getResultRedirect()->setPath('*/*/edit', ['entry_id' => $this->getEntryId()]);
        }
    }

    private function getEntryId()
    {
        return $this->getRequest()->getParam('entry_id');
    }

    private function redirectToReferrer()
    {
        if($this->getCurrentFormId()) {
            return $this->getResultRedirect()
                ->setPath(
                    'plugincompany_contactforms/form/edit',
                    [
                        'form_id' => $this->getCurrentFormId()
                    ]
                );
        }
        return $this->getResultRedirect()->setPath('*/*/');
    }

    private function getCurrentFormId()
    {
        return $this->getRequest()->getParam('edit_form_id');
    }

    /**
     * @return Redirect
     */
    private function getResultRedirect()
    {
        return $this->resultRedirectFactory->create();
    }
}
