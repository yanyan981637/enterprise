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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Ui\DataProvider\File\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class General implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Magezon\ProductAttachments\Model\Config\Source\CategoryOptions
     */
    private $categoriesTree;

    /**
     * General constructor.
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param \Magezon\ProductAttachments\Model\Config\Source\CategoryOptions $categoriesTree
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        \Magezon\ProductAttachments\Model\Config\Source\CategoryOptions $categoriesTree
    ) {
        $this->urlBuilder     = $urlBuilder;
        $this->arrayManager   = $arrayManager;
        $this->categoriesTree = $categoriesTree;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
    	$meta = $this->createNewCategoryModal($meta);
    	$meta = $this->customizeCategoriesField($meta);
        return $meta;
    }

    protected function createNewCategoryModal(array $meta)
    {
        return $this->arrayManager->set(
            'create_category_modal',
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isTemplate' => false,
                            'componentType' => 'modal',
                            'options' => [
                                'title' => __('New Category')
                            ],
                            'imports' => [
                                'state' => '!index=create_category:responseStatus'
                            ]
                        ]
                    ]
                ],
                'children' => [
                    'create_category' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
									'label'         => '',
									'componentType' => 'container',
									'component'     => 'Magento_Ui/js/form/components/insert-form',
									'dataScope'     => '',
                                    'update_url'    => $this->urlBuilder->getUrl('mui/index/render'),
                                    'render_url'    => $this->urlBuilder->getUrl(
                                        'mui/index/render_handle',
                                        [
                                            'handle' => 'productattachments_category_create',
                                            'buttons' => 1
                                        ]
                                    ),
									'autoRender'       => false,
									'ns'               => 'productattachments_new_category_form',
									'externalProvider' => 'productattachments_new_category_form.productattachments_new_category_form_data_source',
									'toolbarContainer' => '${ $.parentName }',
									'formSubmitType'   => 'ajax'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Customize categories field
     *
     * @param array $meta
     * @return array
     */
    protected function customizeCategoriesField($meta)
    {
        $fieldCode = 'category_id';

        $meta['general']['children'][$fieldCode] = [
        	'arguments' => [
        		'data' => [
        			'config' => [
						'dataScope'     => '',
						'breakLine'     => false,
						'formElement'   => 'container',
						'componentType' => 'container',
						'component'     => 'Magento_Ui/js/form/components/group'
        			],
        		],
        	],
        	'children' => [
        		$fieldCode => [
        			'arguments' => [
        				'data' => [
        					'config' => [
								'label'            => __('Categories'),
								'formElement'      => 'select',
								'componentType'    => 'field',
								'component'        => 'Magezon_ProductAttachments/js/components/new-option',
								'filterOptions'    => true,
								'chipsEnabled'     => true,
								'disableLabel'     => true,
                                'multiple'         => false,
								'levelsVisibility' => '1',
								'elementTmpl'      => 'ui/grid/filters/elements/ui-select',
								'options'          => $this->categoriesTree->toOptionArray(false),
        						'listens' => [
        							'index=create_category:responseData' => 'setParsed',
        							'newOption' => 'toggleOptionSelected'
        						],
                                'validation'   => 'required-entry',
        						'config' => [
        							'dataScope' => $fieldCode,
        							'sortOrder' => 10
                                ],
                                'validation' => [
                                    'required-entry' => true,
                                ],
        					]
        				]
        			]
        		],
        		'create_category_button' => [
        			'arguments' => [
        				'data' => [
        					'config' => [
								'title'             => __('New Category'),
								'formElement'       => 'container',
								'additionalClasses' => 'admin__field-small',
								'componentType'     => 'container',
								'component'         => 'Magento_Ui/js/form/components/button',
								'template'          => 'ui/form/components/button/container',
								'actions'           => [
        							[
										'targetName' => 'productattachments_file_form.productattachments_file_form.create_category_modal',
										'actionName' => 'toggleModal'
        							],
        							[
										'targetName' => 'productattachments_file_form.productattachments_file_form.create_category_modal.create_category',
										'actionName' => 'render'
        							],
        							[
										'targetName' => 'productattachments_file_form.productattachments_file_form.create_category_modal.create_category',
										'actionName' => 'resetForm'
        							]
        						],
								'additionalForGroup' => true,
								'provider'           => false,
								'source'             => 'product_details',
								'displayArea'        => 'insideGroup',
								'sortOrder'          => 20,
								'dataScope'          => $fieldCode
        					]
        				]
        			]
        		]
        	]
        ];

        return $meta;
    }
}