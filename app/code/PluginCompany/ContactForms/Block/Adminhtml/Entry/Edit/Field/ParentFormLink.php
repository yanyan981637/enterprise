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
namespace PluginCompany\ContactForms\Block\Adminhtml\Entry\Edit\Field;

class ParentFormLink extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \PluginCompany\ContactForms\Model\EntryRepository
     */
    private $entryRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \PluginCompany\ContactForms\Model\EntryRepository $entryRepository,
        array $data = []
    ){
        $this->request = $request;
        $this->entryRepository = $entryRepository;
        parent::__construct(
            $context,
            $data
        );
        $this->setTemplate('PluginCompany_ContactForms::entry/parentFormLink.phtml');
    }

    private function getFormId()
    {
        return $this->request->getParam('form_id');
    }

}