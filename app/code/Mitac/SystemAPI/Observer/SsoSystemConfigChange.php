<?php

namespace Mitac\SystemAPI\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;

class SsoSystemConfigChange implements ObserverInterface
{
    const XML_PATH_SSO_SETTING = 'mdt/sso_setting/sso_enabled';
    const XML_PATH_ACCOUNT_CONFIRM_SETTING = 'customer/create_account/confirm';

    protected static $rewriteData = [
        'sso_redirect_login_post' => 'customer/account/loginPost',
        'sso_redirect_create' => 'customer/account/create',
        'sso_redirect_create_post' => 'customer/account/createPost',
        'sso_redirect_forgot' => 'customer/account/forgotpassword',
        'sso_redirect_forgot_post' => 'customer/account/forgotpasswordPost',
        'sso_redirect_reset' => 'customer/account/resetpassword',
        'sso_redirect_reset_post' => 'customer/account/resetpasswordPost',
        'sso_redirect_logout_post' => 'customer/account/logoutPost'
    ];

    protected static $persistedRewriteData = [
        'sso_redirect_login' => 'customer/account/ssologin',
        'sso_redirect_logout' => 'customer/account/ssologout'
    ];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    protected $_store;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var UrlRewriteCollection
     */
    protected $urlRewriteCollection;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param UrlRewriteFactory $urlRewriteFactory
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $_store,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteCollection $urlRewriteCollection
    ) {
        $this->request = $request;
        $this->_store = $_store;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewriteCollection = $urlRewriteCollection;
    }

    public function execute(Observer $observer)
    {
        $affectScopeId = $observer->getData('website') ?: 0;
        $changedPath = $observer->getData('changed_paths');

        if ($changedPath && in_array(self::XML_PATH_SSO_SETTING, $changedPath)) {
            $this->handleUrlRewrite($affectScopeId);
        }
    }

    protected function handleUrlRewrite($affectScopeId = 0)
    {
        $addRewriteData = self::$rewriteData;
        $keptRewriteData = self::$persistedRewriteData;

        $affectType = $affectScopeId ? ScopeInterface::SCOPE_WEBSITES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        $mergedRewriteData = array_merge($addRewriteData, $keptRewriteData);

        $defaultValue = (int) $this->scopeConfig->getValue(self::XML_PATH_SSO_SETTING);
        $changedValue = $this->scopeConfig->getValue(
            self::XML_PATH_SSO_SETTING,
            $affectType,
            $affectScopeId
        );

        $affectScope['scope_type'] = (string) $affectType;
        $affectScope['scope_id'] = (int) $affectScopeId;
        $affectScope['scope_default_value'] = (int) $defaultValue;
        $affectScope['scope_changed_value'] = (int) $changedValue;
        $affectScope['scope_confirmed_required'] = (int) !$changedValue;

        try {
            $this->addUrlRewrite();
            $this->keptUrlRewrite();
            $this->saveAffectConfig($affectScope);
        } catch (\Exception $e) {
            // By pass the action if exist url rewrite record(s)
        }
    }

    protected function addUrlRewrite()
    {
        $addRewriteData = self::$rewriteData;
        $keptRewriteData = self::$persistedRewriteData;

        $mergedRewriteData = array_merge($addRewriteData, $keptRewriteData);

        $urlRewriteCollection = $this->urlRewriteCollection
            ->addFieldToFilter('request_path', array('in' => array_values($mergedRewriteData)));

        foreach ($urlRewriteCollection as $urlRewrite) {
            $urlRewrite->delete();
        }

        foreach ($addRewriteData as $key => $data) {
            foreach ($this->_store->getStores() as $store) {
                $finalValue = $this->scopeConfig->getValue(
                    self::XML_PATH_SSO_SETTING,
                    ScopeInterface::SCOPE_WEBSITES,
                    $store->getWebsiteId()
                );

                // Check sso is enabled
                if ($finalValue == 1) {
                    $this->saveRewriteData($store->getId(), $data, 'Customize by SSO Login Redirect');
                }
            }
        }
    }

    protected function keptUrlRewrite()
    {
        $keptRewriteData = self::$persistedRewriteData;

        $urlRewriteCollection = $this->urlRewriteCollection
            ->addFieldToFilter('request_path', array('in' => array_values($keptRewriteData)));

        foreach ($urlRewriteCollection as $urlRewrite) {
            $urlRewrite->delete();
        }

        foreach ($keptRewriteData as $key => $data) {
            foreach ($this->_store->getStores() as $store) {
                $this->saveRewriteData($store->getId(), $data, 'Default block for sso login and logout.');
            }
        }
    }

    protected function saveRewriteData(int $storeId, string $data, string $description = '')
    {
        if ($storeId && $data) {
            $urlRewrite = $this->urlRewriteFactory->create();
            $urlRewrite->setEntityType('custom')
                ->setStoreId($storeId)
                ->setRequestPath($data)
                ->setTargetPath('cms/noroute/index')
                ->setRedirectType(0)
                ->setDescription($description);
            $urlRewrite->save();
        }
    }

    protected function saveAffectConfig($affectScope)
    {
        switch ($affectScope['scope_type']) {
            case ScopeInterface::SCOPE_WEBSITES:
                if ($affectScope['scope_changed_value'] == $affectScope['scope_default_value']) {
                    $this->configWriter->delete(
                        self::XML_PATH_ACCOUNT_CONFIRM_SETTING,
                        ScopeInterface::SCOPE_WEBSITES,
                        $affectScope['scope_id']
                    );
                } else {
                    $this->configWriter->save(
                        self::XML_PATH_ACCOUNT_CONFIRM_SETTING,
                        (int) $affectScope['scope_confirmed_required'],
                        ScopeInterface::SCOPE_WEBSITES,
                        $affectScope['scope_id']
                    );
                }
                break;
            case ScopeConfigInterface::SCOPE_TYPE_DEFAULT:
                $this->configWriter->save(
                    self::XML_PATH_ACCOUNT_CONFIRM_SETTING,
                    (int) $affectScope['scope_confirmed_required'],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
                break;
            default:
                break;
        }
    }
}
