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

class Translation extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \PluginCompany\ContactForms\Model\FormRepository
     */
    private $formRepository;

    protected $_template = 'PluginCompany_ContactForms::form/FieldsTranslation.phtml';

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
     * Get general translation in JSON format
     *
     * @return string
     */
    public function getGeneralTranslationJSON()
    {
        if (!$id = $this->getFormId()) {
            //default content for new form
            $json = '{}';
        }else{
            //form contents of existing form
            $json = $this->formRepository->getByIdOrNew($id)->getGeneralTranslation();
            if(!$json){
                $json = '{}';
            }
        }
        return $json;
    }

    /**
     * Get field translation in JSON format
     *
     * @return string
     */
    public function getTranslationJSON()
    {
        if (!$id = $this->getFormId()) {
            //default content for new form
            $json = '[]';
        }else{
            //form contents of existing form
            $json = $this->formRepository->getByIdOrNew($id)->getTranslation();
            if(!$json){
                $json = '[]';
            }
        }
        return $json;
    }

    public function getStoreviewsJson()
    {
        return json_encode($this->getStoreviews());
    }

    public function getStoreviews()
    {
        $storeData = [];
        foreach($this->_storeManager->getStores() as $store) {
            /** @var \Magento\Store\Model\Store $store  */
            $storeData[] = $store->toArray();
        }
        return $storeData;
    }

}