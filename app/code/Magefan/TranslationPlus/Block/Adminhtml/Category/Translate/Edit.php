<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block\Adminhtml\Category\Translate;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magefan_TranslationPlus';
        $this->_controller = 'adminhtml_category_translate';

        parent::_construct();

        if ($this->_isAllowedAction('Magento_Catalog::categories')) {
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'role' => 'template-save-and',
                    ]
                ],
                -100
            );
            $this->buttonList->add(
                'Save',
                [
                    'label' => __('Save'),
                    'class' => 'save',
                    'data_attribute' => [
                        'role' => 'template-save',
                    ]
                ],
                -100
            );
            $this->buttonList->add(
                'Back',
                [
                    'label' => __('Back'),
                    'class' => 'back',
                    'data_attribute' => [
                        'role' => 'template-back',
                    ]
                ],
                -110
            );

        }

        $this->buttonList->remove('back');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');
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
}
