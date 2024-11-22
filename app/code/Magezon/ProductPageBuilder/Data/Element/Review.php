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

class Review extends \Magezon\ProductPageBuilder\Data\Element
{
    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
	public function prepareGeneralTab()
    {
    	$general = parent::prepareGeneralTab();

            $general->addChildren(
                'display_counter',
                'toggle',
                [
                    'sortOrder'       => 10,
                    'key'             => 'display_counter',
                    'defaultValue'    => false,
                    'templateOptions' => [
                        'label' => __('Display Counter'),
                        'note'  => __('Only work when parent element type is tab')
                    ]
                ]
            );

    	return $general;
    }
}