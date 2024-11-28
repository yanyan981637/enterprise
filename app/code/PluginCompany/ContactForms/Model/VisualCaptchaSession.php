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

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Api\Data\StoreInterface;
use PluginCompany\ContactForms\Model\lib\Visualcaptcha\Captcha;
use PluginCompany\ContactForms\Model\lib\Visualcaptcha\Session;

class VisualCaptchaSession
{
    private $session;
    private $captcha;
    private $namespace;

    private $store;

    /** @var CustomerSession */
    protected $customerSession;

    /**
     * @param StoreInterface $store
     * @param CustomerSession $customerSession
     */
    public function __construct(
        StoreInterface $store,
        CustomerSession $customerSession
    )
    {
        $this->store = $store;
        $this->customerSession = $customerSession;
        return $this;
    }

    public function getCaptcha($namespace)
    {
        $this->namespace = $namespace;

        if(!$this->captcha){
            $this->initCaptcha();
        }
        return $this->captcha;
    }

    private function initCaptcha()
    {
        $this->captcha = new Captcha($this->getSession(), $this->getLocale());
        return $this;
    }

    private function getSession()
    {
        if (!$this->session) {
            $this->initSession();
        }
        return $this->session;
    }

    private function initSession()
    {
        $this->session = new Session(
            ['namespace' => $this->namespace],
            $this->customerSession
        );
        return $this;
    }

    private function getLocale()
    {
        return $this->store->getLocaleCode();
    }

}