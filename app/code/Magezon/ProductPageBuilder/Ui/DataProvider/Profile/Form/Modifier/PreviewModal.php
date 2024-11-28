<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductPageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductPageBuilder\Ui\DataProvider\Profile\Form\Modifier;

use Magento\Ui\Component\Form;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;


use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;

class PreviewModal implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    const IMPORT_OPTIONS_MODAL   = 'preview_modal';
    const SORT_ORDER             = 2000;
    const FORM_NAME              = 'productpagebuilder_profile_form';
    const CUSTOM_OPTIONS_LISTING = 'productpagebuilder_profile_product_grid';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param UrlInterface                $urlBuilder
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registry   = $registry;
    }

    /**
     * Get current profile
     *
     * @return \Magezon\ProductPageBuilder\Model\Profile
     * @throws NoSuchEntityException
     */
    public function getCurrentProfile()
    {
        return $this->registry->registry('current_profile');
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_merge_recursive(
            $meta,
            [
                static::IMPORT_OPTIONS_MODAL => $this->getImportOptionsModalConfig()
            ]
        );

        return $meta;
    }

    /**
     * Get config for modal window "Import Options"
     *
     * @return array
     * @since 101.0.0
     */
    protected function getImportOptionsModalConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'options' => [
                            'title'    => __('Preview'),
                            'subTitle' => __('This function allows you to preview how your profile looks on product page. ')
                        ]
                    ]
                ],
            ],
            'children' => [
                static::CUSTOM_OPTIONS_LISTING => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'insertListing',
                                'dataScope' => static::CUSTOM_OPTIONS_LISTING,
                                'externalProvider' => static::CUSTOM_OPTIONS_LISTING . '.'
                                    . static::CUSTOM_OPTIONS_LISTING . '_data_source',
                                'ns' => static::CUSTOM_OPTIONS_LISTING,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'externalFilterMode' => false,
                                'currentProfileId' => $this->getCurrentProfile()->getId(),
                                'dataLinks' => [
                                    'imports' => false,
                                    'exports' => true
                                ],
                                'exports' => [
                                    'currentProfileId' => '${ $.externalProvider }:params.profile_id',
                                    '__disableTmpl' => ['currentProfileId' => false]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $data['current_profile_id'] = $this->getCurrentProfile()->getId();
        return $data;
    }
}
