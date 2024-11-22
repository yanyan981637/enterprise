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

class Sharing extends \Magezon\ProductPageBuilder\Data\Element
{
    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
	public function prepareGeneralTab()
    {
    	$general = parent::prepareGeneralTab();

            $container1 = $general->addContainerGroup(
                'container1',
                [
                    'sortOrder' => 10
                ]
            );

                $container1->addChildren(
                    'enable_facebook_like',
                    'toggle',
                    [
                        'sortOrder'       => 10,
                        'key'             => 'enable_facebook_like',
                        'defaultValue'    => true,
                        'templateOptions' => [
                            'label' => __('Facebook Like')
                        ]
                    ]
                );

                $container1->addChildren(
                    'enable_facebook_share',
                    'toggle',
                    [
                        'sortOrder'       => 20,
                        'key'             => 'enable_facebook_share',
                        'defaultValue'    => true,
                        'templateOptions' => [
                            'label' => __('Facebook Share')
                        ]
                    ]
                );

                $container1->addChildren(
                    'enable_twitter',
                    'toggle',
                    [
                        'sortOrder'       => 30,
                        'key'             => 'enable_twitter',
                        'defaultValue'    => true,
                        'templateOptions' => [
                            'label' => __('Twiter')
                        ]
                    ]
                );

                $container1->addChildren(
                    'enable_pinterest',
                    'toggle',
                    [
                        'sortOrder'       => 40,
                        'key'             => 'enable_pinterest',
                        'defaultValue'    => true,
                        'templateOptions' => [
                            'label' => __('Pinterest')
                        ]
                    ]
                );

    	return $general;
    }
}