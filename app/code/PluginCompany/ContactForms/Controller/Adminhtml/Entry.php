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

namespace PluginCompany\ContactForms\Controller\Adminhtml;

abstract class Entry extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'PluginCompany_ContactForms::manage_entry';
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \PluginCompany\ContactForms\Model\EntryRepository
     */
    protected $entryRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \PluginCompany\ContactForms\Model\EntryRepository $entryRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->entryRepository = $entryRepository;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('PluginCompany'), __('PluginCompany'))
            ->addBreadcrumb(__('Entry'), __('Entry'));
        return $resultPage;
    }
}
