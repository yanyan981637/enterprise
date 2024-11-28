<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model;

use Amasty\Rolepermissions\Api\Data\RuleInterface;
use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Categories;
use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Products;
use Amasty\Rolepermissions\Helper\Data;
use Amasty\Rolepermissions\Model\Authorization\GetCurrentUserInterface;
use Amasty\Rolepermissions\Model\ResourceModel\Product\Collection\ResourceAdapter as ProductCollectionResourceAdapter;
use Amasty\Rolepermissions\Model\State\NewProductSavingFlag;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Paypal\Model\ResourceModel\Billing\Agreement;
use Magento\Review\Model\ResourceModel\Rating;
use Magento\Review\Model\ResourceModel\Review;
use Magento\SalesRule\Model\ResourceModel\Rule as SalesRule;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @method array|null getRoles() allowed roles
 * @method array|null getProducts() allowed products
 * @method array|null getCategories() allowed categories
 * @method array|null getScopeWebsites() allowed websites
 * @method array|null getScopeStoreviews() allowed stores
 * @method array|null getAttributes() allowed attributes
 * @method $this setRoles(array $roles)
 * @method $this setProducts(array $products)
 * @method $this setCategories(array $categories)
 * @method $this setScopeWebsites(array $websites)
 * @method $this setScopeStoreviews(array $storeviews)
 * @method $this setAttributes(array $attributes)
 */
class Rule extends AbstractModel implements RuleInterface
{
    public const CATALOG = 'CatalogRule';

    public const CART = 'SalesRule';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CollectionFactory
     */
    protected $_productFactory;

    /**
     * @var Data $helper
     */
    protected $helper;

    /**
     * @var NewProductSavingFlag
     */
    private $newProductSavingFlag;

    /**
     * @var ProductCollectionResourceAdapter
     */
    private $productCollectionResourceAdapter;

    /**
     * @var GetCurrentUserInterface
     */
    private $getCurrentUser;

    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        GetCurrentUserInterface $getCurrentUser,
        CollectionFactory $productFactory,
        Data $helper,
        ResourceModel\Rule $ruleResource,
        NewProductSavingFlag $newProductSavingFlag,
        ProductCollectionResourceAdapter $productCollectionResourceAdapter,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->getCurrentUser = $getCurrentUser;
        $this->_productFactory = $productFactory;
        $this->helper = $helper;
        $this->newProductSavingFlag = $newProductSavingFlag;
        $this->productCollectionResourceAdapter = $productCollectionResourceAdapter;

        return parent::__construct($context, $registry, $ruleResource, null, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Rule::class);
        $this->setIdFieldName('id');
    }

    public function loadByRole($roleId): self
    {
        if (!$roleId) {
            return $this;
        }

        $this->load($roleId, 'role_id');

        $websites = $this->getScopeWebsites();

        if (!empty($websites)) {
            $stores = $this->_storeManager->getStores();

            $ids = [];

            foreach ($stores as $id => $store) {
                if (in_array($store->getWebsiteId(), $websites)) {
                    $ids[] = $id;
                }
            }

            $this->setScopeStoreviews($ids);
        }

        return $this;
    }

    public function getPartiallyAccessibleWebsites()
    {
        if (!$this->hasData('partial_ws')) {
            if ($this->getScopeWebsites()) {
                $websites = $this->getScopeWebsites();
            } else {
                if (!$this->getScopeStoreviews()) {
                    $websites = array_keys($this->_storeManager->getWebsites());
                } else {
                    $websitesMap = [];
                    foreach ($this->_storeManager->getStores() as $store) {
                        if (in_array($store->getId(), $this->getScopeStoreviews())) {
                            $websitesMap[$store->getWebsiteId()] = true;
                        }
                    }

                    $websites = array_keys($websitesMap);
                }
            }

            $this->setData('partial_ws', $websites);
        }

        return $this->getData('partial_ws');
    }

    public function restrictProductCollection(ProductCollection $collection): void
    {
        /**
         * To receive correct product ID, there shouldn't be
         * filters applied to the collection on a new product save
         *
         * @see \Magento\CatalogInventory\Model\Stock\StockItemRepository::save
         */
        if ($this->newProductSavingFlag->isSaving()) {
            return;
        }

        $ruleConditions = [];
        $userId = $this->getCurrentUser->execute()->getId();
        $collection->addAttributeToSelect('amrolepermissions_owner', 'left');
        $allowOwn = false;
        $groupOwned = false;

        switch ($this->getProductAccessMode()) {
            case Products::MODE_ANY:
                break;
            case Products::MODE_SELECTED:
                if ($this->getProducts()) {
                    $ruleConditions[] = $this->productCollectionResourceAdapter->formatProductCondition(
                        $this->getProducts()
                    );
                }
                break;
            case Products::MODE_MY:
                $allowOwn = true;
                break;
            case Products::MODE_SCOPE:
                $groupOwned = true;
                break;
        }

        try {
            $fromSelect = $collection->getSelect()->getPart(Select::FROM);
        } catch (\Zend_Db_Select_Exception $e) {
            return;
        }

        if ($this->getCategoryAccessMode() == Categories::MODE_SELECTED
            && !isset($fromSelect[ProductCollectionResourceAdapter::CATEGORY_PRODUCT_TABLE_ALIAS])
            && $this->getCategories()
        ) {
            $ruleConditions[] = $this->productCollectionResourceAdapter->resolveCategoryCondition(
                $collection,
                $this->getCategories()
            );
        }

        if ($this->getScopeAccessMode()
            && !isset($fromSelect[ProductCollectionResourceAdapter::PRODUCT_WEBSITE_TABLE_ALIAS])
            && $partiallyAccessibleWebsites = $this->getPartiallyAccessibleWebsites()
        ) {
            $ruleConditions[] = $this->productCollectionResourceAdapter->resolveWebsiteCondition(
                $collection,
                $partiallyAccessibleWebsites
            );
        }

        if ($ruleConditions) {
            $this->productCollectionResourceAdapter->applyRuleCondition(
                $collection,
                $ruleConditions,
                (int) $userId
            );
        }
        if ($allowOwn) {
            $this->productCollectionResourceAdapter->applyOwnerCondition(
                $collection,
                (int) $userId
            );
        }
        if ($groupOwned) {
            $this->productCollectionResourceAdapter->applyGroupOwnerCondition(
                $collection,
                $this->getCurrentRoleUsersId()
            );
        }

        $collection->getSelect()->distinct();
    }

    public function getAllowedProductIds()
    {
        switch ($this->getProductAccessMode()) {
            case Products::MODE_ANY:
                return false;
            case Products::MODE_SELECTED:
                return $this->getProducts();
            case Products::MODE_SCOPE:
                $users = $this->getCurrentRoleUsersId();

                /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
                $collection = $this->_productFactory->create();
                $collection->addAttributeToFilter('amrolepermissions_owner', ['in' => $users]);

                return $collection->getColumnValues('entity_id');

            case Products::MODE_MY:
                $userId = $this->getCurrentUser->execute()->getId();

                /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
                $collection = $this->_productFactory->create()
                    ->addAttributeToFilter('amrolepermissions_owner', $userId);

                return $collection->getColumnValues('entity_id');
        }

        return false;
    }

    /**
     * Return users for current role
     *
     * @return array
     */
    private function getCurrentRoleUsersId()
    {
        if (!$this->_getData('current_role_user_ids')) {
            $this->setData(
                'current_role_user_ids',
                $this->getCurrentUser->execute()->getRole()->getRoleUsers()
            );
        }

        return $this->_getData('current_role_user_ids');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function checkProductOwner($product)
    {
        $userId = $this->getCurrentUser->execute()->getId();

        return $product->getAmrolepermissionsOwner() == $userId;
    }

    /**
     * @return array
     */
    public function getAllAllowedCategories()
    {
        return $this->_resource->getAllowedCategoriesIds($this->getId());
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function checkProductPermissions($product)
    {
        if ($this->getProductAccessMode() == Products::MODE_ANY || !$this->getProducts() || !$product->getId()) {
            return true;
        }

        return in_array($product->getId(), $this->getProducts());
    }

    public function getCollectionConfig()
    {
        if (!$this->hasData('collection_config')) {
            $config = [
                'external' => [
                    'store' => [
                        Page::class => $this->getResource()->getTable('cms_page_store'),
                        Block::class => $this->getResource()->getTable('cms_block_store'),
                        Rating::class => $this->getResource()->getTable('rating_store'),
                        Review::class => $this->getResource()->getTable('review_store'),
                        Agreement::class => $this->getResource()->getTable('checkout_agreement_store')
                    ],
                    'website' => [
                        CatalogRule::class => $this->getResource()->getTable('catalogrule_website'),
                        SalesRule::class => $this->getResource()->getTable('salesrule_website')
                    ]
                ],
                'internal' => [
                    'store' => [
                        \Magento\Widget\Model\ResourceModel\Widget\Instance::class => 'store_ids',
                    ],
                    'website' => [
                        \Magento\Customer\Model\ResourceModel\Customer::class => 'website_id'
                    ]
                ]
            ];

            if ($this->getLimitOrders()) {
                $config['internal']['store'][\Magento\Sales\Model\ResourceModel\Order::class]
                    = 'main_table.store_id';
            }

            if ($this->getLimitInvoices()) {
                $config['internal']['store'][\Magento\Sales\Model\ResourceModel\Order\Invoice::class]
                    = 'main_table.store_id';
            }

            if ($this->getLimitShipments()) {
                $config['internal']['store'][\Magento\Sales\Model\ResourceModel\Order\Shipment::class] =
                    'main_table.store_id';
            }

            if ($this->getLimitMemos()) {
                $config['internal']['store'][\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class] =
                    'main_table.store_id';
            }

            $this->setData('collection_config', $config);
        }

        return $this->getData('collection_config');
    }

    public function isAttributesInRole($priceRule, $type)
    {
        $isAttributesInRole = true;

        if (is_object($priceRule)) {
            $this->_registry->register('its_amrolepermissions', true, true);

            $priceRuleClone = clone $priceRule;
            $priceRuleAttrCodes = $this->_getRuleAttributeCodes($priceRuleClone, $type);
            $ruleAttrCodes = $this->helper->getAllowedAttributeCodes();

            if (is_array($ruleAttrCodes)) {
                foreach ($priceRuleAttrCodes as $priceRuleAttrCode) {
                    if (!in_array($priceRuleAttrCode, $ruleAttrCodes)) {
                        $isAttributesInRole = false;
                        break;
                    }
                }
            }

            $this->_registry->unregister('its_amrolepermissions');
        }

        return $isAttributesInRole;
    }

    protected function _getRuleAttributeCodes($rule, $type)
    {
        $attributeCodes = [];

        $ruleConditions = $rule->getConditions()->getConditions();

        $productType = 'Magento\\' . $type . '\Model\Rule\Condition\Product';

        $combineType1 = '';
        $combineType2 = '';
        if ($type == self::CART) {
            $combineType1 = \Magento\SalesRule\Model\Rule\Condition\Product\Subselect::class;
            $combineType2 = \Magento\SalesRule\Model\Rule\Condition\Product\Found::class;
        } elseif ($type == self::CATALOG) {
            $combineType1 = \Magento\CatalogRule\Model\Rule\Condition\Combine::class;
        }

        if (!empty($ruleConditions)) {
            foreach ($ruleConditions as $ruleCondition) {
                if ($ruleCondition->getType() == $productType) {
                    $attributeCodes[] = $ruleCondition->getAttribute();
                }
                if ($ruleCondition->getType() == $combineType1
                    || $ruleCondition->getType() == $combineType2
                ) {
                    if (is_array($ruleCondition->getConditions())) {
                        foreach ($ruleCondition->getConditions() as $condition) {
                            //phpcs:ignore
                            $attributeCodes = array_merge(
                                $attributeCodes,
                                $this->_getCombineAttributes($condition, $type)
                            );
                        }
                    }
                }
            }
        }

        return $attributeCodes;
    }

    protected function _getCombineAttributes($condition, $type)
    {
        $productType = 'Magento\\' . $type . '\Model\Rule\Condition\Product';
        $combineType = 'Magento\\' . $type . '\Model\Rule\Condition\Combine';
        $combineAttributes = [];

        if ($condition->getType() == $productType) {
            $combineAttributes[] = $condition->getAttribute();
        } elseif ($condition->getType() == $combineType) {
            foreach ($condition->getConditions() as $subCondition) {
                //phpcs:ignore
                $combineAttributes = array_merge(
                    $combineAttributes,
                    $this->_getCombineAttributes($subCondition, $type)
                );
            }
        }

        return $combineAttributes;
    }

    /**
     * Get allowed admin users
     *
     * @return array
     */
    public function getAllowedUsers()
    {
        if (!$this->getRoles()) {
            return [];
        }

        return $this->getResource()->getAllowedUsersByRoles($this->getRoles());
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleId()
    {
        return $this->_getData(RuleInterface::ROLE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoleId($roleId)
    {
        $this->setData(RuleInterface::ROLE_ID, $roleId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitOrders()
    {
        return $this->_getData(RuleInterface::LIMIT_ORDERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setLimitOrders($limitOrders)
    {
        $this->setData(RuleInterface::LIMIT_ORDERS, $limitOrders);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitInvoices()
    {
        return $this->_getData(RuleInterface::LIMIT_INVOICES);
    }

    /**
     * {@inheritdoc}
     */
    public function setLimitInvoices($limitInvoices)
    {
        $this->setData(RuleInterface::LIMIT_INVOICES, $limitInvoices);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitShipments()
    {
        return $this->_getData(RuleInterface::LIMIT_SHIPMENTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setLimitShipments($limitShipments)
    {
        $this->setData(RuleInterface::LIMIT_SHIPMENTS, $limitShipments);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitMemos()
    {
        return $this->_getData(RuleInterface::LIMIT_MEMOS);
    }

    /**
     * {@inheritdoc}
     */
    public function setLimitMemos($limitMemos)
    {
        $this->setData(RuleInterface::LIMIT_MEMOS, $limitMemos);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductAccessMode()
    {
        return $this->_getData(RuleInterface::PRODUCT_ACCESS_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductAccessMode($productAccessMode)
    {
        $this->setData(RuleInterface::PRODUCT_ACCESS_MODE, $productAccessMode);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryAccessMode()
    {
        return $this->_getData(RuleInterface::CATEGORY_ACCESS_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryAccessMode($categoryAccessMode)
    {
        $this->setData(RuleInterface::CATEGORY_ACCESS_MODE, $categoryAccessMode);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeAccessMode()
    {
        return $this->_getData(RuleInterface::SCOPE_ACCESS_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setScopeAccessMode($scopeAccessMode)
    {
        $this->setData(RuleInterface::SCOPE_ACCESS_MODE, $scopeAccessMode);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAccessMode()
    {
        return $this->_getData(RuleInterface::ATTRIBUTE_ACCESS_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeAccessMode($attributeAccessMode)
    {
        $this->setData(RuleInterface::ATTRIBUTE_ACCESS_MODE, $attributeAccessMode);

        return $this;
    }

    public function getLimitPgridExtra(): int
    {
        return (int)$this->_getData(RuleInterface::LIMIT_PGRID_EXTRA);
    }

    public function setLimitPgridExtra(int $isLimitPgridExtra): RuleInterface
    {
        $this->setData(RuleInterface::LIMIT_PGRID_EXTRA, $isLimitPgridExtra);

        return $this;
    }

    public function getLimitProductSourcesManagement(): int
    {
        return (int)$this->_getData(RuleInterface::LIMIT_PRODUCT_SOURCES_MANAGEMENT);
    }

    public function setLimitProductSourcesManagement(int $isLimitProductSourcesManagement): RuleInterface
    {
        $this->setData(RuleInterface::LIMIT_PRODUCT_SOURCES_MANAGEMENT, $isLimitProductSourcesManagement);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleAccessMode()
    {
        return $this->_getData(RuleInterface::ROLE_ACCESS_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoleAccessMode($roleAccessMode)
    {
        $this->setData(RuleInterface::ROLE_ACCESS_MODE, $roleAccessMode);

        return $this;
    }
}
