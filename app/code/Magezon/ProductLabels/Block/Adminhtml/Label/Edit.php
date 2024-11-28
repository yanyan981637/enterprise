<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Block\Adminhtml\Label;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context  
     * @param \Magento\Framework\Registry           $registry 
     * @param array                                 $data     
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId   = 'label_id';
        $this->_blockGroup = 'Magezon_ProductLabels';
        $this->_controller = 'adminhtml_label';

        parent::_construct();

        if ($this->_isAllowedAction('Magezon_ProductLabels::label_save')) {

            $this->buttonList->add(
                'saveandapply',
                [
                    'label' => __('Save and Apply'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['target' => '#edit_form'],
                        ],
                    ]
                ],
                1000
            );
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                1000
            );
        }
        if ($this->_isAllowedAction('Magezon_ProductLabels::label_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Label'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $label = $this->_coreRegistry->registry('productlabels_label');
        if ($label->getId()) {
            return __("Edit Label '%1'", $this->escapeHtml($label->getName()));
        } else {
            return __('New Label');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * label_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{label_id}}']);
    }
        /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {

        $this->_formScripts[] = "
        require([
        'jquery',
        'mage/backend/form',
        'Magezon_Core/js/jquery.minicolors'
        ], function($){
            $('#saveandapply').click(function(){
                var url = $('#edit_form').attr('action');
                $('#edit_form').attr('action',url + 'apply/1');
                $('#edit_form').submit();
            });
            $('.minicolors').minicolors();
        });";
        return parent::_prepareLayout();
    }
}