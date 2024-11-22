<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block\Adminhtml\Category\Translate\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Store\Model\StoreManagerInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var  Magefan_TranslationPlus::category/translate/edit/form.phtml
     */
    protected $_template = 'Magefan_TranslationPlus::category/translate/edit/form.phtml';

    /**
     * @var CollectionFactory
     */
    private $attributeGroupCollection;

    /**
     * @var FormKey
     */
    protected $formKey;

    protected $storeManagerInterface;

    /**
     * /**
     * @var StoreRepositoryInterface
     */
    private $storeManager;

    /**
     * /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $stores;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CollectionFactory $attributeGroupCollection
     * @param FormKey $formKey
     * @param StoreRepositoryInterface $storeRepository
     * @param CategoryRepository $categoryRepository
     * @param array $data
     * @param StoreManagerInterface|null storeManager
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CollectionFactory $attributeGroupCollection,
        FormKey $formKey,
        StoreRepositoryInterface $storeRepository,
        CategoryRepository $categoryRepository,
        array $data = [],
        StoreManagerInterface $storeManager = null
    ) {
        $this->attributeGroupCollection = $attributeGroupCollection;
        $this->formKey = $formKey;
        $this->storeRepository = $storeRepository;
        $this->categoryRepository = $categoryRepository;

        $this->storeManager = $storeManager ?:  \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'category_translate',
                'enctype' => 'multipart/form-data',
            ]
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return category by id
     *
     * @param int $storeId
     */
    public function getCategory($storeId = null)
    {
        return $this->categoryRepository->get((int) $this->getRequest()->getParam('id'), $storeId);
    }

    /**
     * Return collection of groups
     *
     * @param int $storeId
     * @return array
     */
    public function getGroups($storeId)
    {
        return  $this->attributeGroupCollection->create()
            ->setAttributeSetFilter($this->getCategory($storeId)->getAttributeSetId())
            ->load();
    }

    /**
     * Return form_key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Return collection of stores
     *
     * @return array
     */
    public function getStores()
    {
        $ids = [];
        $category= $this->getCategory(0);
        if ($category) {
            foreach ($category->getStoreIds() as $storeId) {
                $store = $this->storeManager->getStore($storeId);
                if ($store) {
                    $ids[] = $store->getWebsiteId();
                }
            }
        }

        if (null === $this->stores) {
            $this->stores = [];
            $stores = $this->storeRepository->getList();
            $groups = [];
            $c=0;
            foreach ($stores as $store) {
                if (!in_array($store->getWebsite()->getId(), $ids) && $store->getId()!=0) {
                    continue;
                }
                $groups[$store->getGroupId()][] = $store;
            }
            ksort($groups);
            foreach ($groups as $group) {
                foreach ($group as $store) {
                    $this->stores[$store->getCode()] = $store;
                }
            }
        }

        return $this->stores;
    }

    /**
     * Set pageTitle to category name
     *
     * @return layout
     */
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getCategory(0)->getName());
        return parent::_prepareLayout();
    }

    /**
     * Get Html for Groups
     *
     * @param string $attrCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGroupsHtml($attrCode = null)
    {
        $result = [];

        foreach ($this->getStores() as $store) {
            $groupCollection = $this->getGroups($store->getId());
            $oCategory = $this->getCategory($store->getId());
            $gg = [];
            foreach ($groupCollection as $group) {
                $gg[] = $group;
            }

            $group = array_pop($gg);
            if ($group) {
                if ($group['attribute_group_name'] != 'General Information') {
                    continue;
                }
                $attributes = $oCategory->getAttributes($group->getId(), true);
                foreach ($attributes as $key => $attribute) {
                    if (in_array($attribute->getAttributeCode(), ['image', 'available_sort_by', 'default_sort_by', 'filter_price_range'])) {
                        unset($attributes[$key]);
                        continue;
                    }

                    if ($attrCode && $attribute->getAttributeCode() != $attrCode) {
                        unset($attributes[$key]);
                        continue;
                    }

                    $applyTo = $attribute->getApplyTo();
                    if (!$attribute->getIsVisible() || !empty($applyTo) && !in_array($oCategory->getTypeId(), $applyTo)
                        || $attribute->getScope() == 'global') {
                        unset($attributes[$key]);
                    }
                }

                if ($attributes) {
                    $groupBlock = $this->getLayout()
                        ->createBlock(Form\Attributes::class);
                    $output = $groupBlock
                        ->setGroup($group)
                        ->setStore($store)
                        ->setCategory($oCategory)
                        ->setGroupAttributes($attributes)
                        ->toHtml();

                    $output = str_replace('_default"', '_default' . $store->getId() . '"', $output);
                    $result[$store->getId()][] = $output;
                }
            }
        }
        return $result;
    }

    /**
     * Get Template
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->getRequest()->getParam('attr_code')) {
            return 'Magefan_TranslationPlus::category/translate/edit/single-attr-form.phtml';
        }
        return parent::getTemplate();
    }
}
