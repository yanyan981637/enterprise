<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block\Adminhtml\Product\Translate\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Bundle\Model\Product\Type as BundleType;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var  Magefan_TranslationPlus::product/translate/edit/form.phtml
     */
    protected $_template = 'Magefan_TranslationPlus::translate/edit/form.phtml';

    /**
     * @var CollectionFactory
     */
    private $attributeGroupCollection;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var array
     */
    private $stores;

    /**
     * /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CollectionFactory $attributeGroupCollection
     * @param FormKey $formKey
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductRepository $productRepository
     * @param array $data
     */
    public function __construct(
        Context                  $context,
        Registry                 $registry,
        FormFactory              $formFactory,
        CollectionFactory        $attributeGroupCollection,
        FormKey                  $formKey,
        StoreRepositoryInterface $storeRepository,
        ProductRepository        $productRepository,
        array                    $data = []
    )
    {
        $this->attributeGroupCollection = $attributeGroupCollection;
        $this->formKey = $formKey;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;

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
                'method' => 'product_translate',
                'enctype' => 'multipart/form-data',
            ]
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return product by id
     *
     * @param int $storeId
     */
    public function getProduct($storeId)
    {
        return $this->productRepository->getById((int)$this->getRequest()->getParam('id'), false, $storeId, false);
    }

    /**
     * Return current object by id
     *
     * @param int $storeId
     */
    public function getCurrentObject($storeId = null)
    {
        return $this->getProduct($storeId);
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return 'product';
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
            ->setAttributeSetFilter($this->getProduct($storeId)->getAttributeSetId())
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
        $ids = (array)$this->getProduct(0)->getWebsiteIds();

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
     * Set pageTitle to product name
     *
     * @return layout
     */
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getProduct(0)->getName());
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
            $oProduct = $this->getProduct($store->getId());
            $gg = [];
            foreach ($groupCollection as $group) {
                $gg[] = $group;
            }
            $gg = array_reverse($gg);
            foreach ($gg as $group) {

                if ($group['attribute_group_name'] == 'Images'
                    || ($group['attribute_group_name'] == 'Advanced Pricing' && in_array($oProduct->getTypeId(), [ConfigurableType::TYPE_CODE,  BundleType::TYPE_CODE]))
                ) {
                    continue;
                }

                $attributes = $oProduct->getAttributes($group->getId(), true);
                foreach ($attributes as $key => $attribute) {
                    if ($attrCode && $attribute->getAttributeCode() != $attrCode) {
                        unset($attributes[$key]);
                        continue;
                    }

                    if (in_array($oProduct->getTypeId(), [ConfigurableType::TYPE_CODE,  BundleType::TYPE_CODE])) {
                        if ($attribute->getAttributeCode() == 'price') {
                            unset($attributes[$key]);
                            continue;
                        }
                    }

                    if (in_array($attribute->getFrontendInput(), ['select', 'date', 'datetime', 'boolean'])) {
                        unset($attributes[$key]);
                        continue;
                    }

                    $applyTo = $attribute->getApplyTo();
                    if (!$attribute->getIsVisible() || !empty($applyTo) && !in_array($oProduct->getTypeId(), $applyTo)
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
                        ->setProduct($oProduct)
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
            return 'Magefan_TranslationPlus::translate/edit/single-attr-form.phtml';
        }
        return parent::getTemplate();
    }
}
