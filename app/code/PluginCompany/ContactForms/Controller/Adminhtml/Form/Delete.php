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

class Delete extends \PluginCompany\ContactForms\Controller\Adminhtml\Form
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('form_id');
        if ($id) {
            try {
                // init model and delete
                $this->formRepository->deleteById($id);
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Form.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['form_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Form to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
