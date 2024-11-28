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

namespace PluginCompany\ContactForms\Block\Form\Widget;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use PluginCompany\ContactForms\Helper\ConditionsTranslator;
use PluginCompany\ContactForms\Helper\FormHtmlTranslator;
use PluginCompany\ContactForms\Model\Form;
use PluginCompany\ContactForms\Model\FormRepository;
use PluginCompany\ContactForms\Model\Template\Filter;

class View extends Template implements BlockInterface
{

    protected $_template = "form/widget/view.phtml";

    private $formRepository;
    private $filter;
    /**
     * @var FormHtmlTranslator
     */
    private $translator;
    /**
     * @var HttpContext
     */
    private $httpContext;
    /**
     * @var ConditionsTranslator
     */
    private $conditionsTranslator;
    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param FormRepository $formRepository
     * @param Filter $filter
     * @param FormHtmlTranslator $translator
     * @param ConditionsTranslator $conditionsTranslator
     * @param HttpContext $httpContext
     * @param Resolver $localeResolver
     * @param array $data
     */
    public function __construct
    (
        Template\Context $context,
        FormRepository $formRepository,
        Filter $filter,
        FormHtmlTranslator $translator,
        ConditionsTranslator $conditionsTranslator,
        HttpContext $httpContext,
        Resolver $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formRepository = $formRepository;
        $this->filter = $filter;
        $this->translator = $translator;
        $this->httpContext = $httpContext;
        $this->conditionsTranslator = $conditionsTranslator;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Prepare form widget
     * @access protected
     * @return \PluginCompany\ContactForms\Block\Form\Widget\View
     * @throws \Magento\Framework\Exception\LocalizedException
     * @author Milan Simek
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $formId = $this->getData('form_id');
        if ($formId) {
            $form =
                $this->formRepository
                    ->getById($formId)
                    ->setStoreId($this->getCurrentStoreId());
            if ($form->getStatus()) {
                $this->setCurrentForm($form);
            }
        }
        return $this;
    }

    protected function _afterToHtml($html)
    {
        if(!$this->getCurrentForm()) {
            return $html;
        }
        return $this->translator
            ->loadTranslationJson($this->getCurrentForm()->getGeneralTranslation())
            ->translateFormTitle($html);
    }

    public function getJsConfigJson()
    {
        return json_encode($this->getJsConfig());
    }

    public function getJsConfig()
    {
        $form = $this->getCurrentForm();
        $config = [
            'formId' => $form->getId(),
            'theme' => $form->getTheme(),
            'recaptchaKey' => $this->getRecaptchaKey(),
            'uploadUrl' => $this->getUploadUrl(),
            'removeUrl' => $this->getRemoveUrl(),
            'hasVCaptcha' => $form->hasVisualCaptcha(),
            'visualCaptcha' => $this->getVisualCaptchaConfig(),
            'hasReCaptcha' => $form->hasReCaptcha(),
            'invisibleReCaptcha' => $this->_scopeConfig->isSetFlag('plugincompany_contactforms/form/invisible_recaptcha'),
            'invisibleReCaptchaPosition' => $this->_scopeConfig->getValue('plugincompany_contactforms/form/recaptcha_position'),
            'hasUploadField' => $form->hasUploadField(),
            'hasDependendFields' => !empty(json_decode($form->getDependentFields())),
            'dependentFields' => $this->getTranslatedDependentFields(),
            'submitJs' => $form->getArbitraryJs(),
            'beforeSubmitJs' => $form->getBeforesubmitJs(),
            'pageloadJs' => $form->getPageloadJs(),
            'rtl' => (bool)$form->getRtl(),
            'widget' => $this->getEscapedData(),
            'maxUploadSize' => $this->getFileUploadMaxSize(),
            'locale' => $this->getCurrentLocale()
        ];
        return $config;
    }

    public function getRecaptchaKey()
    {
        return $this->getCurrentForm()->getReCaptchaPublicKey();
    }

    public function getCssClasses()
    {
        $classes = $this->getCurrentForm()->getAllCssClasses();

        if($this->getIsPage()){
            $classes[] = 'pc_is_page';
        }
        return implode(' ', $classes);
    }

    public function getStyles()
    {
        if($this->getShowFormAs() == 'form'){
            $styles = $this->getCurrentForm()->getStyles();
            return implode(';', $styles);
        }
        return '';
    }

    public function getWrapperStyles()
    {
        $styles = [];
        if($this->getShowFormAs() != 'form'){
            $styles[] = 'display:none';
        }
        return implode(';', $styles);
    }

    public function getFormHtml()
    {
        $captcha = '<div class="g-recaptcha" data-sitekey="' . $this->getRecaptchaKey() . '"></div>';
        $formHtml = str_replace(['<captcha></captcha>','<legend></legend>'],[$captcha,''], $this->getCurrentForm()->getContactFormHtml());
        $formHtml = preg_replace('/<captcha>.*?<\/captcha>/', $captcha, $formHtml);
        $formHtml = preg_replace('/<legend>(.*?)<\/legend>/','',$formHtml);
        $formHtml = str_replace('col-lg-6 visualcaptcha', 'col-lg-8 visualcaptcha', $formHtml);

        $this->translator
            ->loadTranslationJson($this->getCurrentForm()->getTranslation());
        $formHtml = $this->translator->translate($formHtml);

        $formHtml = $this->getFilter()->rewriteDefaultCustomerValuesToKo($formHtml);
        $formHtml = $this->getFilter()->filter($formHtml);
        $formHtml = $this->getFilter()->prepareFormHtml($formHtml);
        return $formHtml;
    }

    public function getFilter()
    {
        $this->filter->initFrontEndVariables();
        return $this->filter;
    }

    public function isCustomerLoggedIn()
    {
        return $this->httpContext->getValue('customer_logged_in');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('contactforms/form/submit');
    }

    public function getUploadUrl()
    {
        return $this->getUrl('contactforms/form/upload', ['form_id' => $this->getCurrentForm()->getId()]);
    }

    public function getRemoveUrl()
    {
        return $this->getUrl('contactforms/form/removeupload', ['form_id' => $this->getCurrentForm()->getId()]);
    }

    public function getVisualCaptchaConfig()
    {
        return [
            'mainUrl' => $this->getVisualCaptchaMainUrl(),
            'imgUrl' => $this->getVisualCaptchaImgUrl(),
        ];
    }

    public function getVisualCaptchaMainUrl()
    {
        return rtrim($this->getUrl('contactforms/form_visualcaptcha'), '/');
    }

    public function getVisualCaptchaImgUrl()
    {
        return $this->getViewFileUrl('PluginCompany_ContactForms::js/lib/visualcaptcha/img') . '/';
    }

    public function getTranslatedDependentFields()
    {
        $form = $this->getCurrentForm();
        return $this->conditionsTranslator
            ->loadConditionsJson($form->getDependentFields())
            ->loadTranslationJson($form->getTranslation())
            ->getTranslatedConditionsJson()
            ;
    }

    public function getEscapedData()
    {
        $data = [];
        foreach($this->getData() as $k => $v) {
            if(!is_string($v) && !is_int($v)) continue;
            $data[$k] = htmlentities($v, ENT_QUOTES);
        }
        return $data;
    }

    private function getFileUploadMaxSize() {
        static $max_size = -1;

        if ($max_size < 0) {
            $post_max_size = $this->parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }
            $upload_max = $this->parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        $result = $max_size / (1024 * 1024);
        return floor($result);
    }

    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    private function getCurrentLocale()
    {
        return strtolower(str_replace('_', '-', $this->localeResolver->getLocale()));
    }


    private function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return Form
     */
    public function getCurrentForm()
    {
        return $this->getData('current_form');
    }

    public function getUniqueComponentClassName()
    {
        return 'pc_' . str_replace('\\', '_', $this->_nameInLayout);
    }

    public function isCssMinificationEnabled()
    {
        return $this->_scopeConfig->isSetFlag('dev/css/minify_files', 'store')
            && $this->_appState->getMode() != 'developer';
    }


}
