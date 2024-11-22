<?php
namespace PluginCompany\ContactForms\Block\Adminhtml\Form\Widget;

Class Colorpicker extends \Magento\Backend\Block\Template
{
    protected $_elementFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $input = $this->_elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text color");
        if(!$input->getValue()){
            $input->setValue($this->getDefaultValue());
        }
        $input->setAfterElementJs($this->getColorPickerJsForElement($input));
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }
        $element->setValue('');
        $element->setData('after_element_html', $input->getElementHtml());
        return $element;
    }

    private function getColorPickerJsForElement($element)
    {
        $js = '<script type="text/javascript">
                requirejs.config({
                    map: {
                        "*": {
                            "css": "PluginCompany_ContactForms/lib/require-css/css"
                        }
                    }
                });
                require(["jquery","jquery/colorpicker/js/colorpicker","css!jquery/colorpicker/css/colorpicker"], function ($) {
                    var $el = $("#' . $element->getHtmlId() . '");
                    setInterval(function(){
                        if($el.css("display") == "none"){
                            $el.closest("div.field").hide();
                        }else{
                            $el.closest("div.field").show();
                        }
                    },50);
                    $el.css("backgroundColor", "'. $element->getValue() .'");

                    // Attach the color picker
                    $el.ColorPicker({
                        color: "'. $element->getValue() .'",
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            </script><style>.colorpicker {z-index:1000000}</style>';
        return $js;
    }
}
