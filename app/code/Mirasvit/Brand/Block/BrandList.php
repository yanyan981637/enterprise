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

namespace Mirasvit\Brand\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Repository\BrandRepository;
use Mirasvit\Brand\Service\BrandAttributeService;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class BrandList extends Template
{
    private $brandRepository;

    private $brandAttributeService;

    private $config;

    private $layerResolver;

    private $brandFacets;

    public function __construct(
        BrandRepository $brandRepository,
        BrandAttributeService $brandAttributeService,
        Config $config,
        LayerResolver $layerResolver,
        Context $context
    ) {
        $this->brandRepository       = $brandRepository;
        $this->brandAttributeService = $brandAttributeService;
        $this->config                = $config;
        $this->layerResolver         = $layerResolver;

        parent::__construct($context);
    }

    /**
     * Return collection of brands grouped by first letter.
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getBrandsByLetters()
    {
        $collectionByLetters = [];
        $collection          = $this->brandRepository->getList();
        $isShowBrandsWithoutProducts = $this->config->getGeneralConfig()->isShowBrandsWithoutProducts();
        if (!$isShowBrandsWithoutProducts && count($collection) > 0) {
            $this->getBrandFacets(array_values($collection)[0]->getAttributeCode());
	    }

	    if (!$this->brandFacets && !$isShowBrandsWithoutProducts) {
	        return $collectionByLetters;
	    }

        foreach ($collection as $brand) {
            if (!$isShowBrandsWithoutProducts && !array_key_exists($brand->getId(), $this->brandFacets)) {
                continue;
            }

            $letter = strtoupper(mb_substr(trim($brand->getLabel()), 0, 1));

            if (isset($collectionByLetters[$letter])) {
                $collectionByLetters[$letter][$brand->getLabel()] = $brand;
            } else {
                $collectionByLetters[$letter] = [$brand->getLabel() => $brand];
            }
        }

        // sort brands alphabetically
        ksort($collectionByLetters);
        foreach ($collectionByLetters as $letter => $brands) {
            ksort($brands);
            $collectionByLetters[$letter] = $brands;
        }

        return $collectionByLetters;
    }

    /**
     * @param BrandInterface $brand
     *
     * @return bool
     */
    public function canShowImage(BrandInterface $brand)
    {
        return $this->config->getAllBrandPageConfig()->isShowBrandLogo() && $brand->getImage();
    }

    private function getBrandFacets(string $attributeCode): void
    {
        $productCollection = $this->layerResolver->get()->getProductCollection();
        $facets = $productCollection->getFacetedData($attributeCode, true);

        foreach($facets as $facet) {
            $this->brandFacets[$facet['value']] = $facet['count'];
        }
    }
}
