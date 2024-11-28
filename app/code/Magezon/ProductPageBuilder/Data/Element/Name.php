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

class Name extends \Magezon\ProductPageBuilder\Data\Element
{
    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
        $general = parent::prepareGeneralTab();

            $general->addChildren(
                'heading_type',
                'select',
                [
                    'sortOrder'       => 10,
                    'key'             => 'heading_type',
                    'defaultValue'    => 'h1',
                    'templateOptions' => [
                        'label'   => __('Heading Type'),
                        'options' => $this->getHeadingType()
                    ]
                ]
            );

            $general->addChildren(
                'font_size',
                'text',
                [
                    'sortOrder'       => 20,
                    'key'             => 'font_size',
                    'templateOptions' => [
                        'label' => __('Font size')
                    ]
                ]
            );

            $general->addChildren(
                'color',
                'color',
                [
                    'key'             => 'color',
                    'sortOrder'       => 30,
                    'templateOptions' => [
                        'label' => __('Text Color')
                    ]
                ]
            );

            $general->addChildren(
                'line_height',
                'text',
                [
                    'sortOrder'       => 40,
                    'key'             => 'line_height',
                    'templateOptions' => [
                        'label' => __('Line height')
                    ]
                ]
            );

            $general->addChildren(
                'font_weight',
                'text',
                [
                    'sortOrder'       => 50,
                    'key'             => 'font_weight',
                    'templateOptions' => [
                        'label' => __('Font Weight')
                    ]
                ]
            );

        return $general;
    }
}
