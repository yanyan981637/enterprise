<?php
namespace PluginCompany\ContactForms\Block\Adminhtml\Head;
use Magento\Framework\View\Element\Template;

class Cssminification extends Template
{
    public function isCssMinificationEnabled()
    {
        return $this->_scopeConfig->isSetFlag('dev/css/minify_files', 'store')
            && $this->_appState->getMode() != 'developer';
    }
}