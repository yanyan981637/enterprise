<?php
namespace Mitac\SecondaryDevelopmentExtensions\Block\Adminhtml\Blog\Post\Edit\Tab;

use Amasty\Rolepermissions\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier\General;
use Magento\Backend\Block\Widget\Form\Generic;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Post;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Category;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Tag;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Topic;
use DateTimeZone;
use Magento\Framework\Data\Form;
use Mageplaza\Blog\Helper\Image;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
class RewritePost extends Post
{
    protected function _prepareForm(){
        $post = $this->_coreRegistry->registry('mageplaza_blog_post');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('post_');
        $form->setFieldNameSuffix('post');

        // content

        $contentFiledset = $form->addFieldset('content_filedset', [
            'legend' => __('Content'),
            'class'  => 'fieldset-wide',
            'collapsable' => true,
            'collapsed' => true

        ]);

        if ($this->_request->getParam('duplicate')) {
            $contentFiledset->addField('duplicate', 'hidden', [
                'name'  => 'duplicate',
                'value' => 1
            ]);
        }
        $contentFiledset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Name'),
            'title'    => __('Name'),
            'required' => true
        ]);

        $contentFiledset->addField('short_description', 'textarea', [
            'name'  => 'short_description',
            'label' => __('Short Description'),
            'title' => __('Short Description'),
            'note' => __('show on listing'),
        ]);

        $contentFiledset->addField('image', \Mageplaza\Blog\Block\Adminhtml\Renderer\Image::class, [
            'name'  => 'image',
            'label' => __('Image'),
            'title' => __('Image'),
            'path'  => $this->imageHelper->getBaseMediaPath(Image::TEMPLATE_MEDIA_TYPE_POST),
            'note'  => __('The appropriate size is 265px * 250px. show on listing')
        ]);

        $contentFiledset->addField('post_content_type', 'select', [
            'name' => 'post_content_type',
            'label' => __('Content Type'),
            'title' => __('Content Type'),
            'values' => [
                [
                    'value' => 'content',
                    'label' => __('Content')
                ],
                [
                    'value' => 'external_link',
                    'label' => __('External Link')
                ]
            ]
        ]);

        $contentFiledset->addField('post_content', 'editor', [
            'name'   => 'post_content',
            'label'  => __('Content'),
            'title'  => __('Content'),
            'class' => 'hidden',
            'config' => $this->wysiwygConfig->getConfig([
                'add_variables'  => false,
                'add_widgets'    => true,
                'add_directives' => true
            ])
        ]);

        $contentFiledset->addField('post_external_link', 'text', [
            'name'  => 'post_external_link',
            'label' => __('External Link'),
            'title' => __('External Link'),
        ]);

        $contentFiledset->addField(
            'publish_date',
            'date',
            [
                'name'        => 'publish_date',
                'label'       => __('Publish Date'),
                'title'       => __('Publish Date'),
                'date_format' => 'yyyy-MM-dd',
                'timezone'    => false,
                'time_format' => 'hh:mm:ss'
            ]
        );

        // base setting
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Post Information'),
            'class'  => 'fieldset-wide',
            'collapsable' => true,
            'collapsed' => true
        ]);

        $fieldset->addField('url_key', 'text', [
            'name'  => 'url_key',
            'label' => __('URL Key'),
            'title' => __('URL Key')
        ]);
        $fieldset->addField('enabled', 'select', [
            'name'   => 'enabled',
            'label'  => __('Status'),
            'title'  => __('Status'),
            'values' => $this->_status->toOptionArray()
        ]);
        if (!$post->hasData('enabled')) {
            $post->setEnabled(1);
        }
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'hidden', [
                'name'  => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId()
            ]);
        } else {
            /** @var RendererInterface $rendererBlock */
            $rendererBlock = $this->getLayout()->createBlock(Element::class);
            $fieldset->addField('store_ids', 'multiselect', [
                'name'   => 'store_ids',
                'label'  => __('Store Views'),
                'title'  => __('Store Views'),
                'values' => $this->systemStore->getStoreValuesForForm(false, true)
            ])->setRenderer($rendererBlock);

            if (!$post->hasData('store_ids')) {
                $post->setStoreIds(0);
            }
        }

        $fieldset->addField('categories_ids', Category::class, [
            'name'  => 'categories_ids',
            'label' => __('Categories'),
            'title' => __('Categories'),
        ]);
        if (!$post->getCategoriesIds()) {
            $post->setCategoriesIds($post->getCategoryIds());
        }
        $fieldset->addField('author_id', 'select', [
            'name'     => 'author_id',
            'label'    => __('Author'),
            'title'    => __('Author'),
            'required' => true,
            'values'   => $this->_author->toOptionArray()
        ]);

//        $fieldset->addField('topics_ids', Topic::class, [
//            'name'  => 'topics_ids',
//            'label' => __('Topics'),
//            'title' => __('Topics'),
//        ]);
//        if (!$post->getTopicsIds()) {
//            $post->setTopicsIds($post->getTopicIds());
//        }
//
//        $fieldset->addField('tags_ids', Tag::class, [
//            'name'  => 'tags_ids',
//            'label' => __('Tags'),
//            'title' => __('Tags'),
//        ]);
//        if (!$post->getTagsIds()) {
//            $post->setTagsIds($post->getTagIds());
//        }

        $fieldset->addField('in_rss', 'hidden', [
            'name'   => 'in_rss',
            'label'  => __('In RSS'),
            'title'  => __('In RSS'),
            'values' => $this->booleanOptions->toOptionArray(),
        ]);
        $fieldset->addField('allow_comment', 'select', [
            'name'   => 'allow_comment',
            'label'  => __('Allow Comment'),
            'title'  => __('Allow Comment'),
            'values' => $this->booleanOptions->toOptionArray(),
            'value' => 0
        ]);

        $this->_eventManager->dispatch('after_adminhtml_blog_post_info_tab', ['fieldset' => $fieldset]);

        // design
        $designFieldset = $form->addFieldset('design_fieldset', [
            'legend' => __('Design'),
            'class'  => 'fieldset-wide',
            'collapsable' => true,
            'collapsed' => true
        ]);

        $designFieldset->addField('layout', 'select', [
            'name'   => 'layout',
            'label'  => __('Layout'),
            'title'  => __('Layout'),
            'values' => $this->_layoutOptions->toOptionArray(),
        ]);

        if (!$post->getId()) {
            $post->addData([
                'allow_comment' => 1
            ]);
        }

        /** Get the public_date from database */
        if ($post->getData('publish_date')) {
            $publicDateTime = new \DateTime($post->getData('publish_date'), new DateTimeZone('UTC'));
            $publicDateTime->setTimezone(new DateTimeZone($this->_localeDate->getConfigTimezone()));
            $publicDateTime = $publicDateTime->format('m/d/Y H:i:s');
            $post->setData('publish_date', $publicDateTime);
        }

        $form->addValues($post->getData());
        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_blog_edit_form_prepare_form', ['block' => $this]);

        return Generic::_prepareForm();
    }
}
