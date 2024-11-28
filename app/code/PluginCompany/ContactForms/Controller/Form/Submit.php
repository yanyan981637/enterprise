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

namespace PluginCompany\ContactForms\Controller\Form;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Widget\Model\Template\Filter;
use PluginCompany\ContactForms\Helper\FormHtmlTranslator;
use PluginCompany\ContactForms\Model\EntryFactory;
use PluginCompany\ContactForms\Model\EntryRepository;
use PluginCompany\ContactForms\Model\FormRepository;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Helper\Context as ContextHelper;
use PluginCompany\ContactForms\Model\VisualCaptchaSession;


/**
 * @method \Magento\Framework\App\Response\Http getResponse
 * @method \Magento\Framework\App\Request\Http getRequest
 */
class Submit extends Action
{
    CONST CAPTCHA_ERROR = 2;

    protected $resultPageFactory;
    protected $jsonHelper;

    private $contextHelper;
    private $logger;
    private $entry;
    private $entryFactory;
    private $entryRepository;
    private $form;
    private $formRepository;
    private $cleanParams;
    private $customerSession;
    private $visualCaptcha;
    private $lastRecaptchaFrontendData;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var FormHtmlTranslator
     */
    private $translator;
    /**
     * @var Filter
     */
    private $filter;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EntryFactory $entryFactory
     * @param EntryRepository $entryRepository
     * @param FormRepository $formRepository
     * @param Data $jsonHelper
     * @param ContextHelper $contextHelper
     * @param Session $customerSession
     * @param VisualCaptchaSession $vCaptcha
     * @param SubscriberFactory $subscriberFactory
     * @param FormHtmlTranslator $translator
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EntryFactory $entryFactory,
        EntryRepository $entryRepository,
        FormRepository $formRepository,
        Data $jsonHelper,
        ContextHelper $contextHelper,
        Session $customerSession,
        VisualCaptchaSession $vCaptcha,
        SubscriberFactory $subscriberFactory,
        FormHtmlTranslator $translator,
        Filter $filter
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->entryFactory = $entryFactory;
        $this->entryRepository = $entryRepository;
        $this->formRepository = $formRepository;
        $this->logger = $contextHelper->getLogger();
        $this->jsonHelper = $jsonHelper;
        $this->contextHelper = $contextHelper;
        $this->customerSession = $customerSession;
        $this->visualCaptcha = $vCaptcha;
        $this->subscriberFactory = $subscriberFactory;
        $this->translator = $translator;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * Execute view action
     */
    public function execute()
    {
        try {
            if(!$this->getRequest()->isPost()) {
                return $this->redirectToHomePage();
            }
            $this->checkCanContinue();
            $this->runExecute();
        } catch (LocalizedException $e) {
            $this->processError($e);
        } catch (\Exception $e) {
            $this->processError($e);
        } catch (\Throwable $e) {
            $this->processError($e);
        }
    }

    private function redirectToHomePage()
    {
        return $this->_redirect->redirect($this->getResponse(), '/');
    }

    public function checkCanContinue()
    {
        if(!$this->isCaptchaOk() ){
            throw new \Exception(__('Captcha error, please complete the challenge'), self::CAPTCHA_ERROR);
        }
        return $this;
    }

    public function runExecute()
    {
        if(!$this->getRequest()->isPost()){
            $this->_redirect('/');
        }
        if($this->shouldNotifyAdmin()){
            $this->notifyAdmin();
        }
        if($this->shouldNotifyCustomer()){
            $this->notifyCustomer();
        }
        if($this->shouldSaveEntry()){
            $this->saveEntry();
        }
        if($this->shouldSubscribeNewsletter()){
            $this->subscribeNewsletter();
        }
        return $this->sendJsonResponseMessage('success', $this->getSuccessMessage());
    }

    public function shouldNotifyAdmin()
    {
        return (bool)$this->getForm()->getNotifyAdmin();
    }

    public function notifyAdmin()
    {
        $this
            ->getEntryWithSubmissionData()
            ->sendAdminNotification()
        ;
        return $this;
    }

    public function shouldNotifyCustomer()
    {
        return (bool)$this->getForm()->getNotifyCustomer();
    }

    public function notifyCustomer()
    {
        $this
            ->getEntryWithSubmissionData()
            ->sendCustomerNotification()
        ;
        return $this;
    }

    public function shouldSaveEntry()
    {
        return $this->getForm()->getEnableEntries();
    }

    /**
     * @return \PluginCompany\ContactForms\Model\Form
     */
    public function getForm()
    {
        if(!$this->form){
            $this->initForm();
        }
        return $this->form;
    }

    public function initForm()
    {
        $this->form = $this->formRepository->getById(
            $this->getFormIdFromParams()
        );
        return $this;
    }

    public function getFormIdFromParams()
    {
        return (int)$this->getRequest()->getParam('form_id');
    }

    public function saveEntry()
    {
        $this->entryRepository->save(
            $this->getEntryWithSubmissionData()
        );
        return $this;
    }

    /**
     * @return \PluginCompany\ContactForms\Model\Entry
     */
    public function getEntryWithSubmissionData()
    {
        $entry = $this->getEntry();
        if(!$entry->getFormId()){
            $entry->initFromSubmittedData($this->getCleanParameters());
        }
        return $entry;
    }

    /**
     * @return \PluginCompany\ContactForms\Model\Entry
     */
    public function getEntry()
    {
        if(!$this->entry){
            $this->initEntry();
        }
        return $this->entry;
    }

    /**
     * @return \PluginCompany\ContactForms\Model\Entry
     */
    private function initEntry()
    {
        $this->entry = $this->entryFactory->create();
        return $this;
    }

    public function getCleanParameters()
    {
        if(empty($this->cleanParams)){
            $this->prepareCleanParams();
        }
        return $this->cleanParams;
    }

    private function prepareCleanParams()
    {
        foreach($this->getParams() as $k => $param){
            if($this->isVisualCaptchaParam($k)) {
                continue;
            }
            $this->cleanParams[$k] = $this->cleanParam($param);
        }
        $this
            ->addFormUrlToParams()
            ->addUploadDirToParams()
        ;
        return $this;
    }

    private function getParams()
    {
        return $this->getRequest()->getParams();
    }

    private function isVisualCaptchaParam($key)
    {
        if(!$this->getVisualCaptcha()) {
            return false;
        }
        if($key == $this->getVisualCaptchaNamespace()) {
            return true;
        }
        if($key == 'vcaptcha_namespace') {
            return true;
        }
        $frontend = $this->lastRecaptchaFrontendData;
        if(isset($frontend['imageFieldName']) && $frontend['imageFieldName'] == $key) {
            return true;
        }
        if(isset($frontend['audioFieldName']) && $frontend['audioFieldName'] == $key) {
            return true;
        }
        return false;
    }


    private function addFormUrlToParams()
    {
        $this->cleanParams['form_url'] = $this->cleanParam($this->getHttpReferer());
        return $this;
    }

    public function cleanParam($param)
    {
        if(is_array($param)){
            $param = implode(', ',$param);
        }
        return htmlspecialchars($param);
    }

    private function addUploadDirToParams()
    {
        $this->cleanParams['upload_dir'] = $this->getUploadDir();
        $this->unsetUploadDirFromSession();
        return $this;
    }

    private function getUploadDir()
    {
        $session = $this->customerSession;
        return $session->getData($this->getSessionDataKeyForForm());
    }

    private function getSessionDataKeyForForm()
    {
        return 'contactforms_upload_key_' . $this->getFormId();
    }

    private function unsetUploadDirFromSession()
    {
        $session = $this->customerSession;
        $session
            ->unsetData($this->getSessionDataKeyForForm())
            ->unsetData($this->getUploadDirSessionDataKey())
        ;
        return $this;
    }

    private function getUploadDirSessionDataKey()
    {
        return 'contactforms_upload_dir_' . $this->getFormId();
    }

    public function shouldSubscribeNewsletter()
    {
        $params = $this->getCleanParameters();
        return isset($params['newsletter']);
    }

    public function subscribeNewsletter()
    {
        $this->subscriberFactory->create()
            ->subscribe(
                $this->getEntryWithSubmissionData()->getCustomerEmail()
            );
        return $this;
    }

    public function getFormId()
    {
        if(!empty($this->cleanParams['form_id'])) {
            return $this->cleanParams['form_id'];
        }
        return $this->getEntryWithSubmissionData()->getFormId();
    }

    private function getHttpReferer()
    {
        return $this->getRequest()->getServerValue('HTTP_REFERER');
    }

    public function processError($e)
    {
        $this->logger->critical($e);
        $this->sendJsonResponseMessage(
            'error',
            $e->getMessage(),
            $this->getCodeForError($e)
        );
        return $this;
    }

    public function sendJsonResponseMessage($type, $message, $code = null)
    {
        return $this->jsonResponse([
            'type' => $type,
            'message' => $message,
            'error_code' => $code
        ]);
    }

    /**
     * Create json response
     *
     * @param string $responseData
     * @return void
     */
    public function jsonResponse($responseData = '')
    {
        $this->getResponse()
            ->setHeader('X-Robots-Tag', 'none, noindex, nofollow', true)
            ->representJson(
                $this->jsonHelper->jsonEncode($responseData)
            );
    }

    private function getCodeForError($e)
    {
        $code = null;
        if($e->getCode() == self::CAPTCHA_ERROR){
            $code = 'captcha';
        }
        return $code;
    }

    public function getSuccessMessage()
    {
        $this->initTranslator();
        if($this->translator->getTranslatedSuccessMessage()) {
            return $this->filterContent(
                $this->translator->getTranslatedSuccessMessage()
            );
        }
        return $this->filterContent(
            $this->getForm()->getFrontendSuccessMessage()
        );
    }

    private function filterContent($content)
    {
        $filter = $this->getFilter();

        if(method_exists($filter, 'setStrictMode')) {
            $oldMode = $filter->setStrictMode(false);
        }

        $content = $this->getFilter()->filter($content);

        if(isset($oldMode)) {
            $filter->setStrictMode($oldMode);
        }

        return $content;
    }

    private function getFilter()
    {
        $this->filter->setVariables(
            $this->getSubmissionData()
        );
        return $this->filter;
    }

    private function getSubmissionData()
    {
        return
            array_merge(
                $this->getEntryWithSubmissionData()->getData(),
                $this->getEntryWithSubmissionData()->getSubmissionParams()->getData()
            );
    }

    public function initTranslator()
    {
        $this->translator
            ->loadTranslationJson($this->getForm()->getGeneralTranslation());
        return $this;
    }

    public function isCaptchaOk()
    {
        return $this->isReCaptchaOk() && $this->isVisualCaptchaOk();
    }

    /**
     * checks whether the submitted captcha text is OK by validating with Google reCaptcha
     * @return bool
     */
    public function isReCaptchaOk()
    {
        if(!$this->isReCaptchaEnabled()){
            return true;
        }

        try{
            //validate using recaptcha api
            $fields  = 'secret=' . $this->getReCaptchaPrivateKey();
            $fields .= '&response=' . $this->getReCaptchaResponse();
            $fields .= '&remoteip=' . $this->getRemoteAddress();

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_POST, 3);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
            $result = json_decode(curl_exec($ch));
            curl_close($ch);
            return $result->success;
        }catch(Exception $e){
            $this->logger->debug($e);
            return false;
        }
    }

    public function isReCaptchaEnabled()
    {
        return $this->getForm()->hasReCaptcha();
    }

    public function getReCaptchaPrivateKey()
    {
        return $this->getForm()->getReCaptchaPrivateKey();
    }

    public function getReCaptchaResponse()
    {
        $res =  $this->getRequest()->getParam('g-recaptcha-response');
        return $res;
    }

    public function getRemoteAddress()
    {
        return $this->contextHelper->getRemoteAddress()->getRemoteAddress();
    }

    public function isVisualCaptchaOk()
    {
        if(!$this->isVisualCaptchaEnabled()){
            return true;
        }

        return $this->checkVisualCaptcha();
    }

    public function isVisualCaptchaEnabled()
    {
        return $this->getForm()
            ->hasVisualCaptcha();
    }

    public function checkVisualCaptcha()
    {
        $captcha = $this->getVisualCaptcha();
        $frontendData = $captcha->getFrontendData();
        $this->lastRecaptchaFrontendData = $frontendData;
        $params = Array();

        $return = 'error';
        $imgField = 'none';
        if ( ! $frontendData ) {
            $return = 'error_nocaptcha';
        } else {
            $imgField =  $frontendData[ 'imageFieldName' ];
            // If an image field name was submitted, try to validate it
            if ( $imageAnswer = $this->getRequest()->getParam( $frontendData[ 'imageFieldName' ] ) ) {
                if ( $captcha->validateImage( $imageAnswer ) ) {
                    $return = 'success';
                } else {
                    $params[] = 'status=failedImage';
                    $return = 'error_image';
                }
            } else if ( $audioAnswer = $this->getRequest()->getParam( $frontendData[ 'audioFieldName' ] ) ) {
                if ( $captcha->validateAudio( $audioAnswer ) ) {
                    $return = 'success';
                } else {
                    $return = 'error_audio';
                }
            }

            $howMany = count( $captcha->getImageOptions() );
            $captcha->generate( $howMany );
        }

        if(stristr($return, 'error')){
            return false;
        }
        return true;
    }

    public function getVisualCaptcha()
    {
        return $this->visualCaptcha->getCaptcha(
            $this->getVisualCaptchaNamespace()
        );
    }

    public function getVisualCaptchaNamespace()
    {
        return $this->getRequest()
            ->getParam('vcaptcha_namespace');
    }
}
