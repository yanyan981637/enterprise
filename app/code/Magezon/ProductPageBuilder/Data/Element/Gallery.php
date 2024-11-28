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

class Gallery extends \Magezon\ProductPageBuilder\Data\Element
{
    /**
     * Prepare modal components
     */
    public function prepareForm()
    {
        parent::prepareForm();
        $this->prepareGalleryTab();
        return $this;
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGalleryTab()
    {
        $gallery = $this->addTab(
            'gallery',
            [
                'sortOrder'       => 50,
                'templateOptions' => [
                    'label' => __('Gallery Options')
                ]
            ]
        );

            $gallery->addChildren(
                'use_default_theme_settings',
                'toggle',
                [
                    'sortOrder'       => 10,
                    'key'             => 'use_default_theme_settings',
                    'templateOptions' => [
                        'label'   => __('Use Default Theme Settings')
                    ]
                ]
            );

            $container1 = $gallery->addContainerGroup(
                'container1',
                [
                    'sortOrder' => 20,
                    'hideExpression' => 'model.use_default_theme_settings'
                ]
            );

                $container1->addChildren(
                    'gallery_navposition',
                    'select',
                    [
                        'sortOrder'       => 10,
                        'key'             => 'gallery_navposition',
                        'defaultValue'    => 'bottom',
                        'templateOptions' => [
                            'label'   => __('Nav Position'),
                            'options' => $this->getNavPosition(),
                            'tooltip' => __('Position of thumbnails. Default: Bottom')
                        ]
                    ]
                );

                $container1->addChildren(
                    'gallery_nav',
                    'select',
                    [
                        'sortOrder'       => 20,
                        'key'             => 'gallery_nav',
                        'defaultValue'    => 'thumbs',
                        'templateOptions' => [
                            'label'   => __('Nav'),
                            'options' => $this->getNav(),
                            'tooltip' => __('Gallery navigation style. Default: Thumbs')
                        ]
                    ]
                );

                $container1->addChildren(
                    'gallery_loop',
                    'toggle',
                    [
                        'sortOrder'       => 30,
                        'key'             => 'gallery_loop',
                        'defaultValue'    => true,
                        'templateOptions' => [
                            'label'        => __('Loop'),
                            'tooltip'      => __('Gallery navigation loop. Default: true'),
                            'tooltipClass' => 'tooltip-bottom tooltip-bottom-left'
                        ]
                    ]
                );

            $container2 = $gallery->addContainerGroup(
                'container2',
                [
                    'sortOrder' => 30,
                    'hideExpression' => 'model.use_default_theme_settings'
                ]
            );

                $container2->addChildren(
                    'gallery_arrows',
                    'toggle',
                    [
                        'sortOrder'       => 10,
                        'key'             => 'gallery_arrows',
                        'defaultValue'    => true,
                        'templateOptions' => [
                            'label'   => __('Arrows'),
                            'tooltip' => __('Turn on/off arrows on the sides preview. Default: true')
                        ]
                    ]
                );

                $container2->addChildren(
                    'gallery_caption',
                    'toggle',
                    [
                        'sortOrder'       => 20,
                        'key'             => 'gallery_caption',
                        'defaultValue'    => false,
                        'templateOptions' => [
                            'label'   => __('Caption'),
                            'tooltip' => __('Display alt text as image title. Default: false')
                        ]
                    ]
                );

                $container2->addChildren(
                    'gallery_allowfullscreen',
                    'toggle',
                    [
                        'sortOrder'       => 30,
                        'key'             => 'gallery_allowfullscreen',
                        'defaultValue'    => true,
                        'templateOptions' => [
                            'label'        => __('Allow Fullscreen'),
                            'tooltip'      => __('Turn on/off fullscreen. Default: true'),
                            'tooltipClass' => 'tooltip-bottom tooltip-bottom-left'
                        ]
                    ]
                );

            $container3 = $gallery->addContainerGroup(
                'container3',
                [
                    'sortOrder' => 40,
                    'hideExpression' => 'model.use_default_theme_settings'
                ]
            );

                $container3->addChildren(
                    'gallery_navtype',
                    'select',
                    [
                        'sortOrder'       => 10,
                        'key'             => 'gallery_navtype',
                        'defaultValue'    => 'slides',
                        'templateOptions' => [
                            'label'   => __('Nav Type'),
                            'options' => $this->getNavType(),
                            'tooltip' => __('Sliding type of thumbnails. Default: Slides')
                        ]
                    ]
                );

                $container3->addChildren(
                    'gallery_transition_effect',
                    'select',
                    [
                        'sortOrder'       => 20,
                        'key'             => 'gallery_transition_effect',
                        'defaultValue'    => 'slide',
                        'templateOptions' => [
                            'label'   => __('Transition Effect'),
                            'options' => $this->getTransitionEffect(),
                            'tooltip' => __('Sets transition effect for slides changing Default: Slide')
                        ]
                    ]
                );

                $container3->addChildren(
                    'gallery_transition_duration',
                    'number',
                    [
                        'sortOrder'       => 30,
                        'key'             => 'gallery_transition_duration',
                        'defaultValue'    => 500,
                        'templateOptions' => [
                            'label'        => __('Transition Duration'),
                            'tooltip'      => __('Sets transition duration in ms Default: 500'),
                            'tooltipClass' => 'tooltip-bottom tooltip-bottom-left'
                        ]
                    ]
                );

        return $gallery;
    }

    /**
     * @return array
     */
    public function getNavPosition()
    {
        return [
            [
                'label' => 'Top',
                'value' => 'top'
            ],
            [
                'label' => 'Right',
                'value' => 'right'
            ],
            [
                'label' => 'Bottom',
                'value' => 'bottom'
            ],
            [
                'label' => 'Left',
                'value' => 'left'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getNav()
    {
        return [
            [
                'label' => 'False',
                'value' => 'false'
            ],
            [
                'label' => 'Thumbs',
                'value' => 'thumbs'
            ],
            [
                'label' => 'Dots',
                'value' => 'dots'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getNavType()
    {
        return [
            [
                'label' => 'Slides',
                'value' => 'slides'
            ],
            [
                'label' => 'Thumbs',
                'value' => 'thumbs'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getTransitionEffect()
    {
        return [
            [
                'label' => 'Slide',
                'value' => 'slide'
            ],
            [
                'label' => 'Crossfade',
                'value' => 'crossfade'
            ],
            [
                'label' => 'Dissolve',
                'value' => 'dissolve'
            ]
        ];
    }
}
