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
namespace PluginCompany\ContactForms\Model\Form\Source;

class Forms implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \PluginCompany\ContactForms\Model\ResourceModel\Form\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \PluginCompany\ContactForms\Model\ResourceModel\Form\CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }
    /**
     * get possible values
     * @access public
     * @param bool $withEmpty
     * @return array
     * @author Milan Simek
     */
    public function getAllOptions($withEmpty = true){
        $options =  array(
            array(
                'label' => __('New'),
                'value' => 0
            ),
            array(
                'label' => __('Viewed'),
                'value' => 1
            ),
            array(
                'label' => __('In Progress'),
                'value' => 2
            ),
            array(
                'label' => __('On Hold'),
                'value' => 3
            ),
            array(
                'label' => __('Awaiting User'),
                'value' => 4
            ),
            array(
                'label' => __('Answered'),
                'value' => 5
            ),
            array(
                'label' => __('Approved'),
                'value' => 6
            ),
            array(
                'label' => __('Rejected'),
                'value' => 7
            ),
            array(
                'label' => __('Closed'),
                'value' => 8
            ),
            array(
                'label' => __('Complete'),
                'value' => 9
            ),
        );
        $options = $this->getFormOptions();
        if ($withEmpty) {
            array_unshift($options, array('label'=>'', 'value'=>''));
        }
        return $options;

    }

    private function getFormOptions()
    {
        return $this->collectionFactory->create()->toOptionArray();
    }
    /**
     * get options as array
     * @access public
     * @param bool $withEmpty
     * @return string
     * @author Milan Simek
     */
    public function getOptionsArray($withEmpty = true) {
        $options = array();
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    public function toOptionArray($withEmpty = true){
        return $this->getAllOptions($withEmpty);
    }
    /**
     * get option text
     * @access public
     * @param mixed $value
     * @return string
     * @author Milan Simek
     */
    public function getOptionText($value) {
        $options = $this->getOptionsArray();
        if (!is_array($value)) {
            $value = array($value);
        }
        $texts = array();
        foreach ($value as $v) {
            if (isset($options[$v])) {
                $texts[] = $options[$v];
            }
        }
        return implode(', ', $texts);
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getOptionsArray();
    }
}
