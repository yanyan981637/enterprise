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

namespace Mirasvit\Brand\Plugin\Frontend\Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Service\BrandLogoService;
use Mirasvit\Core\Service\CompatibilityService;

class AddBrandLogoPlugin
{
    private $isProductListProduct;

    private $brandLogoService;

    private $brandAttribute;

    public function __construct(
        Config $config,
        BrandLogoService $brandLogoService
    ) {
        $this->isProductListProduct = $config->getBrandLogoConfig()->isProductListBrandLogoEnabled();
        $this->brandLogoService     = $brandLogoService;
        $this->brandAttribute       = $config->getGeneralConfig()->getBrandAttribute();
    }

    /**
     * @param ListProduct $subject
     * @param callable    $proceed
     * @param Product     $product
     *
     * @return string
     */
    public function aroundGetProductDetailsHtml(
        Template $subject,
        callable $proceed,
        Product $product
    ) {
        $html = $proceed($product);

        if (!is_object($product) || !$this->isProductListProduct || !$this->brandAttribute) {
            return $html;
        }

        $product->load($product->getId()); // in some cases attribute's data is absent if the model is not loaded
        $optionId = (int)$product->getData($this->brandAttribute);

        if (!$optionId) {
            return $html;
        }

        $this->brandLogoService->setBrandDataByOptionId($optionId);
        $logo = $this->brandLogoService->getLogoHtml();

        return $html . $logo;
    }

    public function aroundGetData(Template $subject, callable  $proceed, string $key = null)
    {
        if (!$key || $key !== 'viewModel') {
            return $proceed($key);
        }

        if ($subject->getRequest()->getFullActionName() !== 'brand_brand_view') {
            return $proceed($key);
        }

        $version = CompatibilityService::getVersion();

        list($a, $b, $c) = explode('.', $version);

        if ($a == 2 && $b == 4 && $c >= 3) {
            $optionsViewModel = CompatibilityService::getObjectManager()->get('\Magento\Catalog\ViewModel\Product\OptionsData');

            return $optionsViewModel;
        }

        return $proceed($key);
    }
}
