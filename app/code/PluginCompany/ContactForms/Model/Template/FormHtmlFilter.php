<?php
namespace PluginCompany\ContactForms\Model\Template;

use Magento\Customer\Model\Url;

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
class FormHtmlFilter
{
    private $html;
    /**
     * @var Url
     */
    private $customerUrl;

    /**
     * FormHtmlFilter constructor.
     * @param Url $customerUrl
     */
    public function __construct
    (
        Url $customerUrl
    ) {
        $this->customerUrl = $customerUrl;
    }

    public function prepareFormHtmlForDisplay($html)
    {
        $this->html = $html;
        $this
            ->rewriteDataBindAttributes()
            ->replaceLoginLink()
        ;
        return $this->html;
    }

    private function rewriteDataBindAttributes()
    {
        $this->html = str_replace('pc-data-bind', 'data-bind', $this->html);
        return $this;
    }

    private function replaceLoginLink()
    {
        $this->html =
            str_replace(
                [
                    '<log_in_link>',
                    '</log_in_link>'
                ],
                [
                    "<a href='{$this->getLoginLink()}'>",
                    '</a>'
                ],
                $this->html
            );
        return $this;
    }

    private function getLoginLink()
    {
        return $this->customerUrl->getLoginUrl();
    }

}
