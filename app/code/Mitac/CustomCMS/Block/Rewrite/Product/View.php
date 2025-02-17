<?php
namespace Mitac\CustomCMS\Block\Rewrite\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class View extends \Magento\Catalog\Block\Product\View
{
    protected $product;
    protected $attributeSetRepository;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,

        Product $product,
        AttributeSetRepositoryInterface $attributeSetRepository,
        array $data = []
    ) 
    {

        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);
        $this->product = $product;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    public function getAttributeSetName($productId)
    {
        $product = $this->product->load($productId);
        $attributeSet = $this->attributeSetRepository->get($product->getAttributeSetId());
        return $attributeSet->getAttributeSetName();
    }


}
