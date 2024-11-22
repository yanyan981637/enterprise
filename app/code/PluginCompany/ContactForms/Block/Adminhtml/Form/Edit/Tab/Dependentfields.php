<?php
/**
 *
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
 *
 */
namespace PluginCompany\ContactForms\Block\Adminhtml\Form\Edit\Tab;

class Dependentfields extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \PluginCompany\ContactForms\Model\FormRepository
     */
    private $formRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \PluginCompany\ContactForms\Model\FormRepository $formRepository,
        array $data = []
    ){
        $this->request = $request;
        $this->formRepository = $formRepository;
        parent::__construct(
            $context,
            $data
        );
        $this->setTemplate('PluginCompany_ContactForms::form/Dependentfields.phtml');
    }

    private function getFormId()
    {
        $id = $this->request->getParam('form_id');
        if(!$id) {
            $id = $this->request->getParam('copy_form_id');
        }
        return $id;
    }

    /**
     * Get dependent fields in JSON format
     *
     * @return string
     */
    public function getDfieldsJSON()
    {
        if (!$id = $this->getFormId()) {
            //default content for new form
            $json = '[]';
        }else{
            //form contents of existing form
            $json = $this->formRepository->getByIdOrNew($id)->getDependentFields();
            if(!$json){
                $json = '[]';
            }
        }
        return $json;
    }

}