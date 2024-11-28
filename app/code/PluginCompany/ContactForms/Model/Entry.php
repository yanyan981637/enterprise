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

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockFactory;
use PluginCompany\ContactForms\Api\Data\EntryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use PluginCompany\ContactForms\Helper\EntryTranslator;
use PluginCompany\ContactForms\Model\Entry as ModelEntry;
use PluginCompany\ContactForms\Model\Template\Filter;
use Magento\Framework\App\ProductMetadataInterface;

class Entry extends \Magento\Framework\Model\AbstractModel implements EntryInterface
{
    /**    * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'plugincompany_contactforms_entry';
    const CACHE_TAG = 'plugincompany_contactforms_entry';

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'plugincompany_contactforms_entry';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'entry';

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FormRepository
     */
    private $formRepository;

    private $mailer;
    private $filter;
    private $submissionParams;
    private $blockFactory;
    private $ioFile;
    private $directoryList;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    private $uploadedFileUrls = [];
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var EntryTranslator
     */
    private $translator;
    /**
     * @var EntryRepository
     */
    private $entryRepository;


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('PluginCompany\ContactForms\Model\ResourceModel\Entry');
    }

    /**
     * Entry constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $date
     * @param StoreManagerInterface $storeManagerInterface
     * @param \PluginCompany\ContactForms\Model\FormRepository $formRepository
     * @param \PluginCompany\ContactForms\Model\Mailer $mailer
     * @param Filter $filter
     * @param BlockFactory $blockFactory
     * @param DirectoryList $directoryList
     * @param File $ioFile
     * @param ProductMetadataInterface $productMetadata
     * @param UrlInterface $urlBuilder
     * @param EntryTranslator $translator
     * @param EntryRepository $entryRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(Context $context,
        Registry $registry,
        DateTime $date,
        StoreManagerInterface $storeManagerInterface,
        FormRepository $formRepository,
        Mailer $mailer,
        Filter $filter,
        BlockFactory $blockFactory,
        DirectoryList $directoryList,
        File $ioFile,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        EntryTranslator $translator,
        EntryRepository $entryRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->date = $date;
        $this->storeManager = $storeManagerInterface;
        $this->formRepository = $formRepository;
        $this->mailer = $mailer;
        $this->filter = $filter;
        $this->blockFactory = $blockFactory;
        $this->ioFile = $ioFile;
        $this->directoryList = $directoryList;
        $this->productMetadata = $productMetadata;
        $this->urlBuilder = $urlBuilder;
        $this->translator = $translator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->entryRepository = $entryRepository;
    }

    /**
     * Retrieve parent
     * @access public
     * @return null|Form
     * @author Milan Simek
     */
    public function getForm(){
        if (!$this->hasData('_parent_form')) {
            if (!$this->getFormId()) {
                return null;
            }
            else {
                $form = $this->formRepository->getByIdOrNew($this->getFormId());
                if ($form->getId()) {
                    $this->setData('_parent_form', $form);
                }
                else {
                    $this->setData('_parent_form', null);
                }
            }
        }
        return $this->getData('_parent_form');
    }

    public function initFromSubmittedData($params)
    {
        $this
            ->setSubmissionParams($params)
            ->initFormIdFromParams()
        ;
        if($this->getForm()->getEnableEntries()) {
            $this->initIncrementId();
        }
        $this->initFilterParams();

        $this->addData(
            [
                'form_id' => $this->getForm()->getId(),
                'store_id'=> $this->storeManager->getStore()->getStoreId(),
                'customer_name' => $this->getCustomerName(),
                'customer_email' => $this->getCustomerEmail(),
                'customer_bcc'=> $this->getCustomerBcc(),
                'sender_name'=> $this->getSenderName(),
                'sender_email'=> $this->getSenderEmail(),
                'customer_subject'=> $this->getCustomerSubject(),
                'customer_notification'=> $this->getCustomerNotification(),
                'admin_email'=> $this->getAdminEmail(),
                'admin_bcc'=> $this->getAdminBcc(),
                'admin_notification'=> $this->getAdminNotification(),
                'admin_subject'=> $this->getAdminSubject(),
                'admin_sender_name'=> $this->getAdminSenderName(),
                'admin_sender_email'=> $this->getAdminSenderEmail(),
                'admin_reply_to_email'=> $this->getAdminReplyToEmail(),
                'fields'=> $this->getSubmissionParams()->toJson(),
                'upload_dir' => $this->getUploadDir()
            ]
        );
        return $this;
    }

    private function initIncrementId()
    {
        $this
            ->setIncrementId(
                $this->getForm()->getNextEntryIncrementIdCounter()
            )
            ->setIncrementText(
                $this->getForm()->getNextIncrementText()
            )
        ;
        $this
            ->getSubmissionParams()
            ->setReference(
                $this->getIncrementText()
            )
        ;
        return $this;
    }

    public function setSubmissionParams($params)
    {
        $params = new DataObject($params);
        $this->setUploadDir($params->getUploadDir());
        $this->removeUnneededParams($params);
        $this->submissionParams = $params;
        return $this;
    }

    private function removeUnneededParams(DataObject $params)
    {
        $params
            ->unsetData('uid')
            ->unsetData('submitform')
            ->unsetData('upload_dir')
            ->unsetData('g-recaptcha-response')
        ;
    }

    /**
     * @return DataObject
     */
    public function getSubmissionParams()
    {
        if(!$this->submissionParams) {
            $this->submissionParams = new DataObject();
        }
        return $this->submissionParams;
    }

    private function initFilterParams()
    {
        $variables = [];
        $variables['submission'] = $this->getSubmissionParams();
        $variables['submission_overview'] = '##submission_overview##';
        $variables['uploaded_file_links'] = '##uploaded_file_links##';
        $variables['has_uploads'] = $this->hasUploadedFileLinks();
        if($this->getForm()->getEnableEntries()) {
            $variables['submission_id'] = $this->getNextEntityId();
        }
        $variables = array_merge($variables, $this->getSubmissionParams()->toArray());
        $this->filter
            ->initFrontEndVariables()
            ->setVariables($variables);
        return $this;
    }

    private function getNextEntityId()
    {
        return $this->entryRepository->getLastId() + 1;
    }

    public function hasUploadedFileLinks()
    {
        return !empty($this->getUploadedFileUrls());
    }
    public function getUploadedFileUrls()
    {
        if(!$this->uploadedFileUrls) {
            $this->initUploadedFileUrls();
        }
        return $this->uploadedFileUrls;
    }

    public function initUploadedFileUrls()
    {
        $uploads = [];
        foreach($this->getAllUploads() as $file){
            $uploads[] = [
                'title' => $file['text'],
                'url' => $this->getFileUrl($file['text'])
            ];
        }
        $this->uploadedFileUrls = $uploads;
        $this->setData('uploaded_files', $uploads);
        return $this;
    }

    private function getFileUrl($fileName)
    {
        return $this->urlBuilder
                ->getBaseUrl(['_type' =>'media'])
            . 'contactforms/uploads/'
            . $this->getUploadDir()
            . '/' . $fileName;
    }

    public function addHtmlFilterVariables($html, $variablesExcludedFromTable = [])
    {
        $html = $this->addSubmissionTableToString($html, $variablesExcludedFromTable);
        $html = $this->addFileUploadsToString($html);
        return $html;
    }

    public function addSubmissionTableToString($html, $excludedVariables = [])
    {
        $table = $this->getSubmissionOverviewTable($excludedVariables);
        return str_replace(
            '##submission_overview##',
            $table,
            $html
        );
    }

    public function getSubmissionOverviewTable($excludedVariables = [])
    {
        return $this->blockFactory
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('PluginCompany_ContactForms::email/submission_overview_table.phtml')
            ->setSubmissionParams($this->getFilteredSubmissionParamsForTable($excludedVariables))
            ->toHtml()
        ;
    }

    private function getFilteredSubmissionParamsForTable($excludedVariables = [])
    {
        $params = [];
        foreach($this->getSubmissionParams()->getData() as $key => $param){
            if(in_array($key, $excludedVariables)) {
                continue;
            }
            $params[$key] = [
                'label' => $this->getFieldLabel($key),
                'value' => $this->filterVar($param)
            ];
        }
        return $params;
    }

    private function getFieldLabel($key)
    {
        return $this->getTranslator()
            ->translateLabel($key, $this->getForm()->getFieldLabel($key));
    }

    private function getTranslator()
    {
        if(!$this->translator->getFieldTranslations()) {
            $this->initTranslator();
        }
        return $this->translator;
    }

    private function initTranslator()
    {
        $this->translator
            ->loadGeneralTranslationJson(
                $this->getForm()->getGeneralTranslation()
            )
            ->loadFieldTranslationJson(
                $this->getForm()->getTranslation()
            );
        return $this;
    }

    public function addFileUploadsToString($html)
    {
        $links = $this->getUploadedFileLinksHtml();
        return str_replace(
            '##uploaded_file_links##',
            $links,
            $html
        );
    }

    public function getUploadedFileLinksHtml()
    {
        return $this->blockFactory
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('PluginCompany_ContactForms::email/uploaded_file_links.phtml')
            ->setUploadedFileUrls($this->getUploadedFileUrls())
            ->toHtml()
            ;
    }

    private function initFormIdFromParams()
    {
        $this->setFormId(
            $this->getSubmissionParams()->getFormId()
        );
        return $this;
    }

    public function getCustomerName()
    {
        if(!$this->getData('customer_name')){
            $this->initCustomerName();
        }
        return $this->getData('customer_name');
    }

    private function initCustomerName()
    {
        $this->setCustomerName(
            $this->getCustomerNameFromSubmission()
        );
        return $this;
    }

    private function getCustomerNameFromSubmission()
    {
        if ($this->getTranslatedFieldValueFromForm('customer_to_name')) {
            return $this->filterVar($this->getTranslatedFieldValueFromForm('customer_to_name'));
        }
        $params = $this->getSubmissionParams();
        if ($params->getName()) {
            return $params->getName();
        }
        if($params->getFirstname()) {
            return $params->getFirstname() . ' ' . $params->getLastname();
        }
    }

    public function getCustomerEmail()
    {
        if(!$this->getData('customer_email')){
            $this->initCustomerEmail();
        }
        return $this->getData('customer_email');
    }

    private function initCustomerEmail()
    {
        $email = $this->getSubmissionParams()->getEmail();
        if ($this->getTranslatedFieldValueFromForm('customer_to_email')) {
            $email = $this->filterVar($this->getTranslatedFieldValueFromForm('customer_to_email'));
        }
        return $this->setCustomerEmail($email);
    }

    public function getCustomerBcc()
    {
        return $this->getFilteredSubmissionFieldValue(
            'customer_bcc',
            'customer_mail_bcc'
        );
    }

    public function getSenderName()
    {
        return $this->getFilteredSubmissionFieldValue(
            'sender_name',
            'customer_from_name'
        );
    }

    public function getSenderEmail()
    {
        return $this->getFilteredSubmissionFieldValue(
            'sender_email',
            'customer_from_email'
        );
    }

    public function getCustomerSubject()
    {
        return $this->getFilteredSubmissionFieldValue(
            'customer_subject',
            'customer_mail_subject'
        );
    }

    public function getCustomerNotification()
    {
        $html = $this->getFilteredSubmissionFieldValue(
            'customer_notification',
            'customer_mail_content'
        );
        return $this->addHtmlFilterVariables($html, ['form_key', 'form_id']);
    }

    public function getAdminEmail()
    {
        if($email = $this->getData('admin_email')){
            return $email;
        }

        $emails = $this->getConditionalAdminToEmails();
        if(!empty($emails)){
            return implode(',', $emails);
        }

        return $this->getFilteredSubmissionFieldValue(
            'admin_email',
            'admin_to_email'
        );
    }

    private function getConditionalAdminToEmails()
    {
        return $this->getForm()
            ->getConditionalAdminToEmail(
                $this->getSubmissionParams()
            );
    }

    public function getAdminBcc()
    {
        return $this->getFilteredSubmissionFieldValue(
            'admin_bcc',
            'admin_mail_bcc'
        );
    }

    public function getAdminNotification()
    {
        $html = $this->getFilteredSubmissionFieldValue(
            'admin_notification',
            'admin_notification_content'
        );
        return $this->addHtmlFilterVariables($html, ['form_key']);
    }

    public function getAdminSubject()
    {
        return $this->getFilteredSubmissionFieldValue(
            'admin_subject',
            'admin_mail_subject'
        );
    }

    public function getAdminSenderName()
    {
        return $this->getFilteredSubmissionFieldValue(
            'admin_sender_name',
            'admin_from_name'
        );
        return $this;
    }

    public function getAdminSenderEmail()
    {
        return $this->getFilteredSubmissionFieldValue(
            'admin_sender_email',
            'admin_from_email'
        );
        return $this;
    }

    public function getAdminReplyToEmail()
    {
        return $this->getFilteredSubmissionFieldValue(
            'admin_reply_to_email',
            'admin_reply_to_email'
        );
        return $this;
    }

    private function getFilteredSubmissionFieldValue($key, $formKey)
    {
        if(!$this->getData($key)){
            $this->initSubmissionParameterFromForm($key, $formKey);
        }
        return strval($this->getData($key));
    }

    private function initSubmissionParameterFromForm($key, $formKey)
    {
        $value = $this->getTranslatedFieldValueFromForm($formKey);
        return $this->setData($key, $this->filterVar($value));
    }

    private function getTranslatedFieldValueFromForm($key)
    {
        $value = $this->getTranslator()->getGeneralFieldTranslation($key);
        if(!$value) {
            $value = $this->getForm()->{$this->getCamelCaseGetString($key)}();
        }
        return $value;
    }

    private function getCamelCaseGetString($string)
    {
        return 'get' . str_replace('_','', ucwords($string, '_'));
    }

    private function filterVar($value)
    {
        $value = $this->fixTemplateHeaderAndFooter($value);
        return $this->filter->filterForEmail($value);
    }

    private function fixTemplateHeaderAndFooter($value)
    {
        if(empty($value) || !is_string($value)) {
            return $value;
        }
        $value = preg_replace("/(<p.*?>)(.*?header_template.*)(<\/p>)/", "", $value);
        $value = preg_replace("/(<p.*?>)(.*?footer_template.*)(<\/p>)/", "", $value);
        $value = str_replace('{{template config_path="design/email/header_template"}}', '', $value);
        $value = str_replace('{{template config_path="design/email/footer_template"}}', '', $value);
        return $value;
    }


    /**
     * get default values
     * @access public
     * @return array
     * @author Milan Simek
     */
    public function getDefaultValues() {
        $values = [];
        $values['status'] = 1;
        return $values;
    }

    public function sendCustomerNotification()
    {
        if(!$this->isInitialized()){
            $this->throwEmailException();
        }
        try{
            $this->sendCustomerNotificationEmail();
        }
        catch(\Exception $e){
            $this->processEmailNotificationError($e);
        }
        return $this;
    }

    public function sendAdminNotification()
    {
        if(!$this->isInitialized()){
            $this->throwEmailException();
        }
        try{
            $this->sendAdminNotificationEmail();
        }
        catch(\Exception $e){
            $this->processEmailNotificationError($e);
        }
        return $this;
    }

    public function isInitialized()
    {
        return (bool)$this->getFormId();
    }

    private function throwEmailException()
    {
        throw new \Exception('Can\'t send e-mail because because no submission data is set');
    }

    private function sendCustomerNotificationEmail()
    {
        $this->mailer
            ->setEntry($this)
            ->sendCustomerNotification()
        ;
        $this->setCustomerNotificationSent(1);
        return $this;
    }

    private function processEmailNotificationError($e)
    {
        $this->_logger->critical($e->getMessage());
    }

    private function sendAdminNotificationEmail()
    {
        $this->mailer
            ->setEntry($this)
            ->sendAdminNotification()
        ;
        $this->setAdminNotificationSent(1);
        return $this;
    }

    public function getAllUploadedFilePaths()
    {
        $paths = [];
        foreach($this->getAllUploads() as $upload)
        {
            $paths[] = $this->getUploadBaseDir() . $this->getUploadDir() . '/' . $upload['text'];
        }
        return $paths;
    }

    public function getAllUploads()
    {
        if(!$this->getUploadDir()){
            return [];
        }
        return $this->retrieveUploadsList();
    }

    private function retrieveUploadsList()
    {
        $dir = $this->getUploadBaseDir() . $this->getUploadDir();

        if(!is_dir($dir)) return [];

        try{
            $io = $this->ioFile;
            $io->cd($dir);
            return $io->ls();
        }
        catch(\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return [];
    }

    private function getUploadBaseDir()
    {
        $ds = DIRECTORY_SEPARATOR;
        return $this->getMediaDir() . $ds . 'contactforms' . $ds . 'uploads' . $ds;
    }

    private function getMediaDir()
    {
        return $this->directoryList->getPath('media');
    }

    /**
     * Object after load processing. Implemented as public interface for supporting objects after load in collections
     *
     * @return $this
     */
    public function afterLoad()
    {
        $this->getResource()->afterLoad($this);
        $this->_afterLoad();
        if($this->isVersionUnder22()){
            $this->updateStoredData();
        }
        return $this;
    }

    private function isVersionUnder22()
    {
        return version_compare($this->productMetadata->getVersion(), '2.2', '<');
    }

}
