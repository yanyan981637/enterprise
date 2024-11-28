<?php
namespace PluginCompany\ContactForms\Model\lib\Visualcaptcha;
class Session {

    private $namespace = '';
    /** @var  \Magento\Customer\Model\Session */
    private $customerSession;

    /**
     * Session constructor.
     * @param array $namespace
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        $namespace,
        $customerSession
    ) {
        if(empty($namespace) || !is_array($namespace)) {
            $namespace = array('namespace' => 'visualcaptcha');
        }
        if(isset($namespace['namespace'])){
            $namespace = $namespace['namespace'];
        }else{
            $namespace = 'visualcaptcha';
        }
        $this->namespace = $namespace;
        $this->customerSession = $customerSession;
    }


    public function get( $key ) {
        if (!$this->isNamespaceInitialized()) {
            $this->clear();
        }
        if ( isset($this->getSessionData()[ $key ] ) ) {
            return $this->getSessionData()[ $key ];
        }

        return null;
    }

    public function isNamespaceInitialized()
    {
        return is_array($this->getSessionData());
    }

    public function getSessionData()
    {
        return $this->customerSession->getData($this->namespace);
    }

    public function clear() {
        $this->customerSession
            ->setData($this->namespace, []);
    }

    public function set( $key, $value ) {
        if (!$this->isNamespaceInitialized() ) {
            $this->clear();
        }
        $sessionData = $this->getSessionData();
        $sessionData[$key] = $value;
        $this->customerSession->setData($this->namespace, $sessionData);
    }
}
