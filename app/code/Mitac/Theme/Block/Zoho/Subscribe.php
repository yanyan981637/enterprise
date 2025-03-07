<?php
namespace Mitac\Theme\Block\Zoho;
use Magento\Framework\View\Element\Template;
use Mitac\Theme\Enum\ZohoSubscribe;
class Subscribe extends Template
{

    protected $_template = 'Magento_Theme::html/footer/subscribe.phtml';

    public function __construct(
        Template\Context $context,
    ){
        parent::__construct($context);
    }

    public function getSubmitUrl(){
        return $this->_urlBuilder->getUrl('mitac_theme/zoho/subscribe');
    }

    public function getTypeOptions(){
        return ZohoSubscribe::getTypeOptions();
    }

}
