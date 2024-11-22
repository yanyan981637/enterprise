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
 * ContactForms default helper
 *
 * @category    PluginCompany
 * @package     PluginCompany_ContactForms
 * @author      Milan Simek
 */
namespace PluginCompany\ContactForms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data
    extends AbstractHelper {
    /**
     * convert array to options
     * @access public
     * @param $options
     * @return array
     * @author Milan Simek
     */
    public function convertOptions($options){
        $converted = [];
        foreach ($options as $option){
            if (isset($option['value']) && !is_array($option['value']) && isset($option['label']) && !is_array($option['label'])){
                $converted[$option['value']] = $option['label'];
            }
        }
        return $converted;
    }
    
    public function formatArrayAsFormHtmlOptions($array)
    {
        if(!count($array)){
            return '';
        }
        return implode('||||', $array);
    }
}
