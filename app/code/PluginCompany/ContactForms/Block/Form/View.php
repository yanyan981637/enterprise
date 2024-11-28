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

/**
 * Form view block
 *
 * @category    PluginCompany
 * @package     PluginCompany_ContactForms
 * @author      Milan Simek
 */

namespace PluginCompany\ContactForms\Block\Form;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class View
    extends Template {
    private $filterProvider;


    public function __construct(
        Context  $context,
        FilterProvider $filterProvider,
        array $data = []
    )
    {
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    public function toHtml()
    {
        $html = $this->filterProvider
            ->getBlockFilter()
            ->filter(
                '{{widget type="PluginCompany\ContactForms\Block\Form\Widget\View" form_id="' . $this->getFormId() . '" show_form_as="form" is_page="1"}}'
            );
        return $html;
    }

    public function getFormId()
    {
        return $this->_request->getParam('form_id');
    }

}
