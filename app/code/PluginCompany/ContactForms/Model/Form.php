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

namespace PluginCompany\ContactForms\Model;

use PluginCompany\ContactForms\Api\Data\FormInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\DataObject;

use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use PluginCompany\ContactForms\Model\ResourceModel\Entry\Collection;

class Form extends AbstractModel implements FormInterface
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'plugincompany_contactforms_form';
    const CACHE_TAG = 'plugincompany_contactforms_form';
    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'plugincompany_contactforms_form';
    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'form';
    /**
     * @var DateTime
     */
    private $modelDate;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Collection
     */
    private $entryCollection;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('PluginCompany\ContactForms\Model\ResourceModel\Form');
    }

    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $modelDate,
        ScopeConfigInterface $configScopeConfigInterface,
        Collection $entryCollection,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->modelDate = $modelDate;
        $this->scopeConfig = $configScopeConfigInterface;
        $this->entryCollection = $entryCollection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getAllCssClasses()
    {
        $classes = ['pccf'];
        $classes[] = $this->getTheme();
        $classes[] = $this->getLabelStyleClass();
        if($this->getRtl()) {
            $classes[] = 'rtl';
        }
        $classes = array_merge($classes, $this->getCssClassesAsArray());
        return $classes;
    }

    private function getCssClassesAsArray()
    {
        return $this->getData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
    }

    public function getStyles()
    {
        $styles = [];
        if($w = $this->getMaxWidth()){
            $styles[] = 'max-width:' . $w;
        }
        return $styles;
    }


    /**
     * returns the css  class of the form wrapper based on frontend design choice
     * @return string
     */
    public function getWrapClass()
    {
        if ($this->getFormWrapper() == 'well') {
            return 'well';
        }
        if ($this->getFormWrapper() == 'panel_danger') {
            return 'panel panel-danger';
        }
        if ($this->getFormWrapper() == 'panel_success') {
            return 'panel panel-success';
        }
        if ($this->getFormWrapper() == 'panel_info') {
            return 'panel panel-info';
        }
        if ($this->getFormWrapper() == 'panel_warning') {
            return 'panel panel-warning';
        }
        if ($this->getFormWrapper() == 'panel_primary') {
            return 'panel panel-primary';
        }
        if ($this->getFormWrapper() == 'panel_default') {
            return 'panel panel-default';
        }
    }

    public function getLabelStyleClass()
    {
        if(!$this->getLabelStyle()) return '';
        return $this->getLabelStyleOptions()[$this->getLabelStyle()];
    }

    private function getLabelStyleOptions()
    {
        return [
            1 => '',
            2 => 'labelabove',
            3 => 'hidelabel labelabove'
        ];
    }

    /**
     * Returns true if form is using the 'panel' design option
     * @return bool
     */
    public function isPanel()
    {
        $isPanel = explode('_', $this->getFormWrapper());
        if ($isPanel[0] == 'panel') {
            return true;
        }
        return false;
    }

    public function getNotifyAdmin()
    {
        if ($this->getData('notify_admin') === "1") {
            return true;
        }elseif($this->getData('notify_admin') === "2"){
            return (bool)$this->scopeConfig->getValue('plugincompany_contactforms/admin_notification/enable', 'store');
        }
        return false;
    }

    public function getNotifyCustomer()
    {
        if ($this->getData('notify_customer') === "1") {
            return true;
        }elseif($this->getData('notify_customer') === "2"){
            return (bool)$this->scopeConfig->getValue('plugincompany_contactforms/customer_notification/enable', 'store');
        }
        return false;
    }

    public function getCustomerFromName()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'customer_from_name',
            'plugincompany_contactforms/customer_notification/from_name',
            $this->scopeConfig->getValue('trans_email/ident_general/name', 'store')
        );
    }

    public function getCustomerFromEmail()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'customer_from_email',
            'plugincompany_contactforms/customer_notification/from_email',
            $this->scopeConfig->getValue('trans_email/ident_general/email', 'store')
        );
    }

    public function getCustomerMailSubject()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'customer_mail_subject',
            'plugincompany_contactforms/customer_notification/subject',
            __('Thank you for contacting us')
        );
    }

    public function getCustomerMailBcc()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'customer_mail_bcc',
            'plugincompany_contactforms/customer_notification/bcc',
            null
        );
    }

    public function getCustomerMailContent()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'customer_mail_content',
            'plugincompany_contactforms/customer_notification/content',
            ''
        );
    }

    public function getAdminFromName()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'admin_from_name',
            'plugincompany_contactforms/admin_notification/from_name',
            $this->scopeConfig->getValue('trans_email/ident_general/name', 'store')
        );
    }

    public function getAdminFromEmail()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'admin_from_email',
            'plugincompany_contactforms/admin_notification/from_email',
            $this->scopeConfig->getValue('trans_email/ident_general/email', 'store')
        );
    }

    /**
     * Get all conditional admin to e-mails
     * @author Milan
     * @param DataObject $params
     * @return bool|Array
     */
    public function getConditionalAdminToEmail(DataObject $params){

        $params = $params->toArray();
        // get the conditonal fields array
        if(empty($this->getConditToEmail())){
            return false;
        }

        $condEmail = $this->getConditToEmail();
        // loop through all conditional e-mails
        $conditionalMatches = [];
        foreach($this->getConditToEmail() as $field){
            // get the field ID
            $fieldName = $field['field_id'];
            if(empty($params[$fieldName]) ) {
                continue;
            }

            if($field['match_type'] == 'exact' && $params[$fieldName] == $field['value']){
                $conditionalMatches[] = $field['email'];
            }elseif($field['match_type'] == 'partial' && stristr($params[$fieldName],$field['value'])){
                $conditionalMatches[] = $field['email'];
            }
        }
        if(!empty($conditionalMatches)){
            return $conditionalMatches;
        }
        return false;
    }

    public function getAdminToEmail()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'admin_to_email',
            'plugincompany_contactforms/admin_notification/to_email',
            $this->scopeConfig->getValue('trans_email/ident_general/email', 'store')
        );
    }

    public function getAdminMailSubject()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'admin_mail_subject',
            'plugincompany_contactforms/admin_notification/subject',
            __('New contact form submission')
        );
    }

    public function getAdminReplyToEmail()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'admin_reply_to_email',
            null,
            $this->getCustomerToEmail()
        );
    }

    public function getAdminMailBcc()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'admin_mail_bcc',
            'plugincompany_contactforms/admin_notification/bcc',
            null
        );
    }

    public function getAdminNotificationContent()
    {
        return $this->getFormValueOrFallbackIfEmpty(
            'admin_notification_content',
            'plugincompany_contactforms/admin_notification/content',
            ''
        );
    }

    private function getFormValueOrFallbackIfEmpty($dataKey, $fallbackConfigKey, $fallback = false)
    {
        if($formValue = $this->getData($dataKey)) {
            return $formValue;
        }elseif($fallbackConfigKey && $storeValue = $this->scopeConfig->getValue($fallbackConfigKey, 'store')){
            return $storeValue;
        }
        return $fallback;
    }

    public function getNextIncrementText(){
        $incrementID = $this->getNextEntryIncrementIdCounter();
        $prefix = $this->getEntryIncrementPrefix();
        $incrementText = $prefix . sprintf('%08d', $incrementID);
        return $incrementText;
    }

    public function getEntryIncrementPrefix(){
        if($prefix = $this->getData('entry_increment_prefix')){
            return $prefix;
        }
        return $this->getId();
    }
    public function getNextEntryIncrementIdCounter()
    {
        return $this->getEntryIncrementIdCounter() + 1;
    }

    public function increaseEntryIncrementIdCounter()
    {
        $this->setEntryIncrementIdCounter(
            $this->getNextEntryIncrementIdCounter()
        );
        return $this;
    }

    public function hasVisualCaptcha()
    {
        if(stristr($this->getContactFormHtml(),'visualcaptcha')){
            return true;
        }
        return false;
    }

    public function hasReCaptcha()
    {
        if(stristr($this->getContactFormHtml(),'<captcha>')){
            return true;
        }
        return false;
    }

    public function getReCaptchaPublicKey()
    {
        return $this->getStoreConfig('plugincompany_contactforms/form/recaptcha_key');
    }

    public function getReCaptchaPrivateKey()
    {
        return $this->getStoreConfig('plugincompany_contactforms/form/recaptcha_private_key');
    }

    private function getStoreConfig($value)
    {
        return $this->scopeConfig->getValue($value, ScopeInterface::SCOPE_STORE);
    }

    public function hasUploadField()
    {
        return (bool)stristr($this->getContactFormHtml(), 'pcc_upload');
    }

    public function getUploadElements()
    {
        $uploadElements = [];
        foreach($this->getFormElements() as $element){
            if(isset($element['type']) && $element['type'] == 'upload'){
                $uploadElements[] = $element;
            }
        }
        return $uploadElements;
    }

    /**
     * @param string $fieldId
     * @return string
     */
    public function getFieldLabel($fieldId)
    {
        foreach($this->getFormElements() as $field)
        {
            if(!isset($field['fields']['id']['value'])) {
                continue;
            }
            if(!isset($field['fields']['label']['value'])) {
                continue;
            }
            if($fieldId == $field['fields']['id']['value']) {
                return $field['fields']['label']['value'];
            }
        }
        return ucfirst(str_replace('_', ' ', $fieldId));
    }


    public function getFormElements()
    {
        return json_decode($this->getContactFormJson(),true);
    }

}
