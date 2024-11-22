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
namespace PluginCompany\ContactForms\Model\Template;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\PageCache\Model\Config;

class PrivateContentFilter
{

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;
    /**
     * @var Config
     */
    private $pageCacheConfig;

    private $html;

    public function __construct(
        CurrentCustomer $currentCustomer,
        Config $pageCacheConfig
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->pageCacheConfig = $pageCacheConfig;
    }

    public function rewriteDefaultCustomerValuesToKo($html)
    {
        $this->html = $html;

        if(!$this->currentCustomer->getCustomerId()) {
            return $this->html;
        }

        if(!$this->pageCacheConfig->isEnabled()) {
            return $this->html;
        }

        $this
            ->rewriteInputValues()
            ->rewriteTextareaValues()
            ->rewriteOptionsText()
            ->rewritePlaceholderValues()
            ->rewriteTextToSpan()
        ;

        return $this->html;
    }

    private function rewriteInputValues()
    {
        $regex = "/value=\"(?:.(?!value=\"|<\/))*?{{var (?:customer|billing_address|shipping_address).*?}}.*?\"/";
        $this->html = preg_replace_callback($regex, function($match) {
            $result = $this->replaceVarNotation($match[0], true, "'+", "+'");
            $result = str_replace('value="', 'data-bind="value: fields().customer ? \'', $result);
            $result = substr($result, 0, -1);
            $result .= "' : ''\"";
            return $result;
        }, $this->html);
        return $this;
    }

    private function rewritePlaceholderValues()
    {
        $regex = "/(<(?:.(?!<))*?)(placeholder=\")((?:.(?!placeholder=\"|<\/))*?{{var (?:customer|billing_address|shipping_address).*?}}.*?)(\")(.*?>)/";
        $this->html = preg_replace_callback($regex, function($match) {
            $placeholderAttrHtml = $match[2] . $match[3] . $match[4];
            $result = str_replace($placeholderAttrHtml, '', $match[0]);

            $value = "attr: {placeholder: fields().customer ? '";
            $value .= $this->replaceVarNotation($match[3], true, "'+", "+'");
            $value .= "' : '' }";

            if(stristr($result, 'data-bind')) {
                $result = str_replace('data-bind="', 'data-bind="' . $value . ', ', $result);
                return $result;
            }
            $result = substr($result, 0, -1);
            $result .= ' data-bind="' . $value . '">';
            return $result;
        }, $this->html);
        return $this;
    }

    private function rewriteTextareaValues()
    {
        $regex = "/<textarea(?:.(?!<\/textarea>))*?{{var (?:customer|billing_address|shipping_address).*?}}.*?<\/textarea>/";
        $this->html = preg_replace_callback($regex, function($match) {

            $partsRegex = "/(<textarea.*?>)(.*?)(<\/textarea>)/";
            preg_match($partsRegex, $match[0],$parts);

            $result = str_replace('>', ' data-bind="value: fields().customer ? \'', $parts[1]);
            $result .= $this->replaceVarNotation($parts[2], true, "'+", "+'");
            $result .= "' : ''\">";
            $result .= $parts[3];
            return $result;
        }, $this->html);
        return $this;
    }

    private function rewriteOptionsText()
    {
        $regex = "/<option(?:.(?!<\/option>))*?{{var (?:customer|billing_address|shipping_address).*?}}.*?<\/option>/";
        $this->html = preg_replace_callback($regex, function($match) {
            $partsRegex = "/(<option.*?data-bind=\")(.*?)(\".*?>)(.*?)(<\/option>)/";
            preg_match($partsRegex, $match[0],$parts);
            $parts[2] = $parts[2] . ',' . str_replace('value:', 'text:', $parts[2]);

            unset($parts[0]);
            unset($parts[4]);
            return implode('', $parts);
        }, $this->html);
        return $this;
    }

    private function rewriteTextToSpan()
    {
        $html = $this->html;
        $html = $this->replaceVarNotation($html, true, '<span data-bind="text: ', '"></span>');
        $this->html = $html;
        return $this;
    }

    private function replaceVarNotation(
        $string,
        $addFieldMarkers = false,
        $fieldStartMarker = '#fieldstart#',
        $fieldEndMarker = '#fieldend#'
    ) {
        $regex = "/{{var (customer|billing_address|shipping_address)\.(get)(.*?)\(\)}}/";
        $string = preg_replace_callback($regex, function($match) use($addFieldMarkers, $fieldEndMarker, $fieldStartMarker) {
            $result = '';
            if($addFieldMarkers) {
                $result .= $fieldStartMarker;
            }
            $result .= "getValue('" . $match[1] . '.';
            $result .= strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $match[3]));
            $result .= "')";
            if($addFieldMarkers) {
                $result .= $fieldEndMarker;
            }
            return $result;
        }, $string);
        return $string;
    }

}
