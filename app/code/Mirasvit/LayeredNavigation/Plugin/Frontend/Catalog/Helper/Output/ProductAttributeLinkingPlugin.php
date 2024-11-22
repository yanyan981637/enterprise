<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\LayeredNavigation\Plugin\Frontend\Catalog\Helper\Output;


use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManager;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Mirasvit\LayeredNavigation\Model\Config\SeoConfigProvider;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;

class ProductAttributeLinkingPlugin
{
    private $configProvider;

    private $seoConfigProvider;

    private $request;

    private $categoryCollectionFactory;

    private $storeManager;

    private $urlFinder;

    private $urlBuilder;

    public function __construct(
        ConfigProvider $configProvider,
        SeoConfigProvider $seoConfigProvider,
        RequestInterface $request,
        CollectionFactory $categoryCollectionFactory,
        StoreManager $storeManager,
        UrlFinderInterface $urlFinder,
        UrlInterface $urlBuilder
    ) {
        $this->configProvider            = $configProvider;
        $this->seoConfigProvider         = $seoConfigProvider;
        $this->request                   = $request;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager              = $storeManager;
        $this->urlFinder                 = $urlFinder;
        $this->urlBuilder                = $urlBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterProductAttribute(
        AbstractHelper $subject,
        ?string $result,
        ?Product $product,
        ?string $attributeHtml,
        ?string $attributeName
    ): ?string {
        if (!$product || !$attributeName || !$this->isApplicable($result)) {
            return $result;
        }

        /** @var $attribute \Magento\Eav\Api\Data\AttributeInterface */
        try {
            $attribute = $product->getResource()->getAttribute($attributeName);
        } catch (\Exception $e) {
            $attribute = null;
        }

        if (!$attribute || !$this->isApplicableAttribute($attribute)) {
            return $result;
        }

        $optionIds = $product->getData($attributeName);

        if (!$optionIds) {
            return $result;
        }

        $optionIds = explode(',', $optionIds);
        $options   = [];

        foreach ($attribute->getOptions() as $opt) {
            if (in_array($opt->getValue(), $optionIds)) {
                $options[$opt->getValue()] = $opt->getLabel();
            }
        }

        asort($options);

        /** @var CategoryInterface|null $category */
        $category = null;

        if ($catIds = $product->getAvailableInCategories()) {
            $collection = $this->categoryCollectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $catIds]);

            $collection->getSelect()->order('level DESC')->limit(1);

            $category = $collection->getFirstItem();
        } else {
            return $result;
        }

        if (!$category || !$category->getId()) {
            return $result;
        }

        $categoryRewrite = $this->urlFinder->findOneByData(
            [
                'entity_id'     => $category->getId(),
                'entity_type'   => 'category',
                'store_id'      => $this->storeManager->getStore()->getId(),
                'redirect_type' => 0
            ]
        );

        $categoryPath = $categoryRewrite
            ? $categoryRewrite->getRequestPath()
            : 'catalog/category/view/id/' . $category->getId();

        $resultArray = [];

        foreach ($options as $optionId => $optionHtml) {
            $resultArray[] = '<a href="' . $this->buildFilteredUrl((string)$categoryPath, (string)$attributeName, (int)$optionId)
                . '" target="' . $this->configProvider->getProductAttributeLinkTarget()
                . '" rel="' . $this->seoConfigProvider->getRelAttribute()
                . '">' . $optionHtml . '</a>';
        }

        return count($resultArray) ? implode(', ', $resultArray) : $result;
    }

    private function isApplicable(?string $result): bool
    {
        if (
            !$this->configProvider->isProductAttributeLinkingEnabled()
            || !$result
            || $this->request->getFullActionName() !== 'catalog_product_view'
        ) {
            return false;
        }

        return true;
    }

    private function isApplicableAttribute(?AttributeInterface $attribute): bool
    {
        if (!$attribute || !$attribute->getIsFilterable()) {
            return false;
        }

        return true;
    }

    private function buildFilteredUrl(string $categoryPath, string $attributeCode, int $optionId): string
    {
        if ($this->configProvider->isSeoFiltersEnabled()) {
            $friendlyUrlService = ObjectManager::getInstance()->create('Mirasvit\SeoFilter\Service\FriendlyUrlService');

            return $friendlyUrlService->getUrl(
                $attributeCode,
                (string)$optionId,
                false,
                $this->urlBuilder->getUrl($categoryPath)
            );
        }

        return $this->urlBuilder->getUrl($categoryPath, ['_query' => [$attributeCode => $optionId]]);
    }
}
