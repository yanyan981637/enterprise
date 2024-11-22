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

namespace PluginCompany\ContactForms\Block\Adminhtml\Form\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CopyButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getModelId()) {
            $data = [
                'label' => __('Copy Form'),
                'class' => '',
                'sort_order' => 100,
                'on_click' => 'window.location.href = "' . $this->getCopyUrl() . '"'
            ];
        }
        return $data;
    }

    /**
     * Get URL for copy button
     *
     * @return string
     */
    public function getCopyUrl()
    {
        return $this->getUrl('*/*/edit', ['copy_form_id' => $this->getModelId()]);
    }
}
