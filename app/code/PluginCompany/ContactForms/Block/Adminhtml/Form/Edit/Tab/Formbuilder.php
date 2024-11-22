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

/**
 * Created by PhpStorm.
 * User: milan
 * Date: 10/14/14
 * Time: 2:02 AM
 */
namespace PluginCompany\ContactForms\Block\Adminhtml\Form\Edit\Tab;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use PluginCompany\ContactForms\Model\FormFactory;

class Formbuilder extends Template {
    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var FormFactory
     */
    private $formRepository;


    public function __construct(
        Context $context,
        \PluginCompany\ContactForms\Model\FormRepository $formRepository,
        array $data = []
    ) {
        $this->setTemplate('PluginCompany_ContactForms::form/Formbuilder.phtml');
        parent::__construct($context, $data);
        $this->requestInterface = $context->getRequest();
        $this->formRepository = $formRepository;
    }

    /**
     * Get form contents in JSON format for Bootstrap Form Builder
     *
     * @return string
     */
    public function getFormContentsJSON()
    {
        if (!$id = $this->getFormId()) {
            //default content for new form
            $json = '[{"title":"form name"},{"title":"Paragraph text","fields":{"id":{"label":"ID / Name","type":"input","value":"text","name":"id"},"content":{"label":"Content","type":"textarea","value":"Please drop us a line below and we\'ll get in touch with you shortly!","name":"content"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Paragraph text"}},{"title":"Text Input","fields":{"id":{"label":"ID / Name","type":"input","value":"name","name":"id"},"label":{"label":"Label Text","type":"input","value":"Name","name":"label"},"placeholder":{"label":"Placeholder","type":"input","value":"","name":"placeholder"},"defaultval":{"label":"Default Value","type":"input","value":"{{var customer.getName()}}","name":"defaultval"},"helptext":{"label":"Help Text","type":"input","value":"","name":"helptext"},"required":{"label":"Required","type":"checkbox","value":true,"name":"required"},"validation":{"label":"Validation","type":"select","value":[{"value":"","selected":true,"label":"None"},{"value":"email","selected":false,"label":"email"},{"value":"street","selected":false,"label":"Street (Letters, numbers, spaces or #)"},{"value":"phoneLax","selected":false,"label":"Phone"},{"value":"fax","selected":false,"label":"Fax"},{"value":"url","selected":false,"label":"URL (including http://)"},{"value":"clean-url","selected":false,"label":"Domain URL"},{"value":"number","selected":false,"label":"Number (number and dot)"},{"value":"digits","selected":false,"label":"Digits (only numbers)"},{"value":"alpha","selected":false,"label":"Letters only (a-z or A-Z)"},{"value":"alphanum","selected":false,"label":"Alphanumeric (Letters or numbers only)"},{"value":"date-au","selected":false,"label":"Date (dd/mm/yyyy)"}],"name":"validation"},"inputheight":{"label":"Input Height","type":"select","value":[{"value":"input-sm","selected":false,"label":"Small"},{"value":"","selected":true,"label":"Default"},{"value":"input-lg","selected":false,"label":"Large"}],"name":"inputheight"},"inputsize":{"label":"Input Size","type":"select","value":[{"value":"col-md-2","selected":false,"label":"Mini"},{"value":"col-md-3","selected":false,"label":"Small"},{"value":"col-md-4","selected":false,"label":"Medium"},{"value":"col-md-5","selected":false,"label":"Large"},{"value":"col-md-6","selected":true,"label":"Xlarge"},{"value":"col-md-8","selected":false,"label":"Xxlarge"}],"name":"inputsize"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Text Input"}},{"title":"Text Input","fields":{"id":{"label":"ID / Name","type":"input","value":"email","name":"id"},"label":{"label":"Label Text","type":"input","value":"E-mail","name":"label"},"placeholder":{"label":"Placeholder","type":"input","value":"","name":"placeholder"},"defaultval":{"label":"Default Value","type":"input","value":"{{var customer.getEmail()}}","name":"defaultval"},"helptext":{"label":"Help Text","type":"input","value":"","name":"helptext"},"required":{"label":"Required","type":"checkbox","value":true,"name":"required"},"validation":{"label":"Validation","type":"select","value":[{"value":"","selected":false,"label":"None"},{"value":"email","selected":true,"label":"email"},{"value":"street","selected":false,"label":"Street (Letters, numbers, spaces or #)"},{"value":"phoneLax","selected":false,"label":"Phone"},{"value":"fax","selected":false,"label":"Fax"},{"value":"url","selected":false,"label":"URL (including http://)"},{"value":"clean-url","selected":false,"label":"Domain URL"},{"value":"number","selected":false,"label":"Number (number and dot)"},{"value":"digits","selected":false,"label":"Digits (only numbers)"},{"value":"alpha","selected":false,"label":"Letters only (a-z or A-Z)"},{"value":"alphanum","selected":false,"label":"Alphanumeric (Letters or numbers only)"},{"value":"date-au","selected":false,"label":"Date (dd/mm/yyyy)"}],"name":"validation"},"inputheight":{"label":"Input Height","type":"select","value":[{"value":"input-sm","selected":false,"label":"Small"},{"value":"","selected":true,"label":"Default"},{"value":"input-lg","selected":false,"label":"Large"}],"name":"inputheight"},"inputsize":{"label":"Input Size","type":"select","value":[{"value":"col-md-2","selected":false,"label":"Mini"},{"value":"col-md-3","selected":false,"label":"Small"},{"value":"col-md-4","selected":false,"label":"Medium"},{"value":"col-md-5","selected":false,"label":"Large"},{"value":"col-md-6","selected":true,"label":"Xlarge"},{"value":"col-md-8","selected":false,"label":"Xxlarge"}],"name":"inputsize"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Text Input"}},{"title":"Text Input","fields":{"id":{"label":"ID / Name","type":"input","value":"phone","name":"id"},"label":{"label":"Label Text","type":"input","value":"Phone","name":"label"},"placeholder":{"label":"Placeholder","type":"input","value":"","name":"placeholder"},"defaultval":{"label":"Default Value","type":"input","value":"","name":"defaultval"},"helptext":{"label":"Help Text","type":"input","value":"","name":"helptext"},"required":{"label":"Required","type":"checkbox","value":false,"name":"required"},"validation":{"label":"Validation","type":"select","value":[{"value":"","selected":true,"label":"None"},{"value":"email","selected":false,"label":"email"},{"value":"street","selected":false,"label":"Street (Letters, numbers, spaces or #)"},{"value":"phoneLax","selected":false,"label":"Phone"},{"value":"fax","selected":false,"label":"Fax"},{"value":"url","selected":false,"label":"URL (including http://)"},{"value":"clean-url","selected":false,"label":"Domain URL"},{"value":"number","selected":false,"label":"Number (number and dot)"},{"value":"digits","selected":false,"label":"Digits (only numbers)"},{"value":"alpha","selected":false,"label":"Letters only (a-z or A-Z)"},{"value":"alphanum","selected":false,"label":"Alphanumeric (Letters or numbers only)"},{"value":"date-au","selected":false,"label":"Date (dd/mm/yyyy)"}],"name":"validation"},"inputheight":{"label":"Input Height","type":"select","value":[{"value":"input-sm","selected":false,"label":"Small"},{"value":"","selected":true,"label":"Default"},{"value":"input-lg","selected":false,"label":"Large"}],"name":"inputheight"},"inputsize":{"label":"Input Size","type":"select","value":[{"value":"col-md-2","selected":false,"label":"Mini"},{"value":"col-md-3","selected":false,"label":"Small"},{"value":"col-md-4","selected":false,"label":"Medium"},{"value":"col-md-5","selected":false,"label":"Large"},{"value":"col-md-6","selected":true,"label":"Xlarge"},{"value":"col-md-8","selected":false,"label":"Xxlarge"}],"name":"inputsize"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Text Input"}},{"title":"Multiple Radios Inline","fields":{"name":{"label":"Group Name","type":"input","value":"contact","name":"name"},"label":{"label":"Label Text","type":"input","value":"Contact me by","name":"label"},"required":{"label":"Required","type":"checkbox","value":false,"name":"required"},"radios":{"label":"Radios","type":"textarea-split","value":["E-mail","Phone"],"name":"radios"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Multiple Radios Inline"}},{"title":"Select Basic","fields":{"id":{"label":"ID / Name","type":"input","value":"department","name":"id"},"label":{"label":"Label Text","type":"input","value":"Question for","name":"label"},"required":{"label":"Required","type":"checkbox","value":false,"name":"required"},"empty":{"label":"Empty item","type":"input","value":"-- Please Select","name":"empty"},"options":{"label":"Options","type":"textarea-split","value":["Sales","Customer Service","Technical Support"],"name":"options"},"inputheight":{"label":"Input Height","type":"select","value":[{"value":"input-sm","selected":false,"label":"Small"},{"value":"","selected":true,"label":"Default"},{"value":"input-lg","selected":false,"label":"Large"}],"name":"inputheight"},"inputsize":{"label":"Input Size","type":"select","value":[{"value":"col-md-2","selected":false,"label":"Mini"},{"value":"col-md-3","selected":false,"label":"Small"},{"value":"col-md-4","selected":false,"label":"Medium"},{"value":"col-md-5","selected":false,"label":"Large"},{"value":"col-md-6","selected":true,"label":"Xlarge"},{"value":"col-md-8","selected":false,"label":"Xxlarge"}],"name":"inputsize"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Select Basic"}},{"title":"Text Area","fields":{"id":{"label":"ID / Name","type":"input","value":"message","name":"id"},"label":{"label":"Label Text","type":"input","value":"Message","name":"label"},"textarea":{"label":"Placeholder","type":"input","value":"","name":"textarea"},"contents":{"label":"Default Value","type":"textarea","value":"","name":"contents"},"required":{"label":"Required","type":"checkbox","value":true,"name":"required"},"validation":{"label":"Validation","type":"select","value":[{"value":"","selected":true,"label":"None"},{"value":"email","selected":false,"label":"email"},{"value":"street","selected":false,"label":"Street (Letters, numbers, spaces or #)"},{"value":"phoneLax","selected":false,"label":"Phone"},{"value":"fax","selected":false,"label":"Fax"},{"value":"url","selected":false,"label":"URL (including http://)"},{"value":"clean-url","selected":false,"label":"Domain URL"},{"value":"number","selected":false,"label":"Number (number and dot)"},{"value":"digits","selected":false,"label":"Digits (only numbers)"},{"value":"alpha","selected":false,"label":"Letters only (a-z or A-Z)"},{"value":"alphanum","selected":false,"label":"Alphanumeric (Letters or numbers only)"},{"value":"date-au","selected":false,"label":"Date (dd/mm/yyyy)"}],"name":"validation"},"inputheight":{"label":"Input Height","type":"select","value":[{"value":"input-sm","selected":false,"label":"Small"},{"value":"","selected":true,"label":"Default"},{"value":"input-lg","selected":false,"label":"Large"}],"name":"inputheight"},"inputsize":{"label":"Input Size","type":"select","value":[{"value":"col-md-2","selected":false,"label":"Mini"},{"value":"col-md-3","selected":false,"label":"Small"},{"value":"col-md-4","selected":false,"label":"Medium"},{"value":"col-md-5","selected":false,"label":"Large"},{"value":"col-md-6","selected":true,"label":"Xlarge"},{"value":"col-md-8","selected":false,"label":"Xxlarge"}],"name":"inputsize"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Text Area"}},{"title":"Single Button","fields":{"id":{"label":"ID / Name","type":"input","value":"submitform","name":"id"},"label":{"label":"Label Text","type":"input","value":"","name":"label"},"buttonlabel":{"label":"Button Label","type":"input","value":"Submit","name":"buttonlabel"},"buttontype":{"label":"Button Type","type":"select","value":[{"value":"btn-default","selected":false,"label":"Default"},{"value":"btn-primary","selected":true,"label":"Primary"},{"value":"btn-info","selected":false,"label":"Info"},{"value":"btn-success","selected":false,"label":"Success"},{"value":"btn-warning","selected":false,"label":"Warning"},{"value":"btn-danger","selected":false,"label":"Danger"}],"name":"buttontype"},"cssclass":{"label":"Custom CSS Class(es)","type":"input","value":"","name":"cssclass"},"title":"Single Button"}}]' ;
        }else{
            //form contents of existing form
            $json = $this->formRepository->getByIdOrNew($id)->getContactFormJson();
        }
        return $json;
    }

    private function getFormId()
    {
        $id = $this->requestInterface->getParam('form_id');
        if(!$id) {
            $id = $this->requestInterface->getParam('copy_form_id');
        }
        return $id;
    }


}