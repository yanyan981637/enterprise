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
namespace PluginCompany\ContactForms\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ReCaptchaPosition extends AbstractOption implements ArrayInterface
{

    /**
     * Get Options
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'bottomright' => __('Bottom Right'),
            'bottomleft' => __('Bottom Left'),
            'inline' => __('Inline')
        ];
    }
}