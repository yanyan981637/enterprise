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

namespace Magezon\ProductPageBuilder\Data\Element;

class Attribute extends \Magezon\ProductPageBuilder\Data\Element
{
    /**
     * @var \Magezon\ProductPageBuilder\Model\Source\ProductAttribute
     */
    protected $productAttribute;

    /**
     * @param \Magezon\Builder\Data\FormFactory                         $formFactory
     * @param \Magezon\Builder\Helper\Data                              $dataHelper
     * @param \Magezon\ProductPageBuilder\Model\Source\ProductAttribute $productAttribute
     * @param array                                                     $data
     */
    public function __construct(
        \Magezon\Builder\Data\FormFactory $formFactory,
        \Magezon\Builder\Helper\Data $dataHelper,
        \Magezon\ProductPageBuilder\Model\Source\ProductAttribute $productAttribute,
        array $data = []
    ) {
        parent::__construct($formFactory, $dataHelper, $data);
        $this->productAttribute = $productAttribute;
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
        $general = parent::prepareGeneralTab();

            $general->addChildren(
                'attribute',
                'uiSelect',
                [
                    'sortOrder'       => 10,
                    'key'             => 'attribute',
                    'templateOptions' => [
                        'label'   => __('Attribute'),
                        'options' => $this->productAttribute->toOptionArray()
                    ]
                ]
            );

            $general->addChildren(
                'show_label',
                'toggle',
                [
                    'sortOrder'       => 20,
                    'key'             => 'show_label',
                    'templateOptions' => [
                        'label' => __('Show Label')
                    ]
                ]
            );

        return $general;
    }
}
