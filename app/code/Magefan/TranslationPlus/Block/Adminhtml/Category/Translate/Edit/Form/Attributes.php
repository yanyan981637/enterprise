<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block\Adminhtml\Category\Translate\Edit\Form;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * Prepare attributes form
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
        $group = $this->getGroup();
        $store = $this->getStore();
        if ($group) {
            /** @var \Magento\Framework\Data\Form $form */
            $form = $this->_formFactory->create();
            $category = $this->getCategory();
            $isWrapped = $this->_coreRegistry->registry('use_wrapper');
            if (!isset($isWrapped)) {
                $isWrapped = true;
            }
            $isCollapsable = $isWrapped && $group->getAttributeGroupCode() == 'category-details';
            $legend = $isWrapped ? __($group->getAttributeGroupName())  : null;
            // Initialize category object as form property to use it during elements generation
            $form->setDataObject($category);
            $fieldset = $form->addFieldset(
                'group-fields-' . $group->getAttributeGroupCode(),
                ['class' => 'user-defined', 'legend' => $legend, 'collapsable' => $isCollapsable]
            );
            $attributes = $this->getGroupAttributes();
            $this->_setFieldset($attributes, $fieldset, ['gallery']);

            // Add new attribute controls if it is not an image tab
            if (!$form->getElement(
                'media_gallery'
            ) && $this->_authorization->isAllowed(
                'Magento_Catalog::attributes_attributes'
            ) && $isWrapped
            ) {
                $attributeCreate = $this->getLayout()->createBlock(
                    \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create::class
                );

                $attributeCreate->getConfig()->setAttributeGroupCode(
                    $group->getAttributeGroupCode()
                )->setTabId(
                    'group_' . $group->getId()
                )->setGroupId(
                    $group->getId()
                )->setStoreId(
                    $form->getDataObject()->getStoreId()
                )->setAttributeSetId(
                    $form->getDataObject()->getAttributeSetId()
                )->setTypeId(
                    $form->getDataObject()->getTypeId()
                )->setProductId(
                    $form->getDataObject()->getId()
                );

                $attributeSearch = $this->getLayout()->createBlock(
                    \Magefan\TranslationPlus\Block\Adminhtml\Category\Translate\Edit\Form\Attributes\Search::class
                )->setGroupId(
                    $group->getId()
                )->setProduct(
                    $category
                )->setGroupCode(
                    $group->getAttributeGroupCode()
                );
                $attributeSearch->setAttributeCreate($attributeCreate->toHtml());
                $fieldset->setHeaderBar($attributeSearch->toHtml());
            }

            $values = $category->getData();
            // Set default attribute values for new category or on attribute set change
            if (!$category->getId() || $category->dataHasChangedFor('attribute_set_id')) {
                foreach ($attributes as $attribute) {
                    if (!isset($values[$attribute->getAttributeCode()])) {
                        $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }
            if ($category->hasLockedAttributes()) {
                foreach ($category->getLockedAttributes() as $attribute) {
                    $element = $form->getElement($attribute);
                    if ($element) {
                        $element->setReadonly(true, true);
                        $element->lock();
                    }
                }
            }
            $form->addValues($values);
            if ($store->getId()=='0') {
                $form->setFieldNameSuffix('-1');
            } else {
                $form->setFieldNameSuffix($store->getId());
            }
            $this->_eventManager->dispatch(
                'adminhtml_catalog_category_edit_prepare_form',
                ['form' => $form, 'layout' => $this->getLayout()]
            );

            $this->setForm($form);
        }
    }

    /**
     * Retrieve additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = [
            'price' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price::class,
            'weight' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight::class,
            'gallery' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery::class,
            'image' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::class,
            'boolean' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean::class,
            //'textarea' => \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::class, // DISABLED FOR NOW (There is way to add it, more info in task 6305)
        ];

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_catalog_category_edit_element_types', ['response' => $response]);

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }
        return $result;
    }
}
