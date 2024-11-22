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

use Magento\Framework\App\Request\DataPersistorInterface;

class Edit extends \PluginCompany\ContactForms\Controller\Adminhtml\Form
{

    protected $resultPageFactory;
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \PluginCompany\ContactForms\Model\FormRepository $formRepository
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PluginCompany\ContactForms\Model\FormRepository $formRepository,
        DataPersistorInterface $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $coreRegistry, $formRepository);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('form_id');
        $model = $this->formRepository->getByIdOrNew($id);
        if ($id && !$model->getId()) {
            return $this->handleFormNotExists();
        }
        if($this->getRequest()->getParam('copy_form_id')) {
            $model
                ->setData($this->getCopiedFormData())
                ->setEntityId(null)
                ->setId(null);
            $this->dataPersistor->set('plugincompany_contactforms_form', $model->getData());
        }
        $this->_coreRegistry->register('plugincompany_contactforms_form', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Form') : __('New Form'),
            $id ? __('Edit Form') : __('New Form')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Contact Forms'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Form'));
        return $resultPage;
    }

    private function handleFormNotExists()
    {
        $this->messageManager->addErrorMessage(__('This Form no longer exists.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    private function getCopiedFormData()
    {
        $model = $this->formRepository->getByIdOrNew(
            $this->getRequest()->getParam('copy_form_id')
        );
        return $model->getData();
    }
}
