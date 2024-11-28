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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Ui\DataProvider\Post\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magezon\Blog\Model\Author\Source\AuthorList;
use Magezon\Blog\Model\Category\Source\CategoriesTree;
use Magezon\Blog\Model\Tag\Source\TagList;

class General implements ModifierInterface
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
     * @var CategoriesTree
     */
    private $categoriesTree;

    /**
     * @var TagList
     */
    private $tagList;

    /**
     * @var AuthorList
     */
    private $authorList;

    /**
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param CategoriesTree $categoriesTree
     * @param TagList $tagList
     * @param AuthorList $authorList
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        CategoriesTree $categoriesTree,
        TagList $tagList,
        AuthorList $authorList
    ) {
        $this->urlBuilder     = $urlBuilder;
        $this->arrayManager   = $arrayManager;
        $this->categoriesTree = $categoriesTree;
        $this->tagList        = $tagList;
        $this->authorList     = $authorList;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
    	$meta = $this->createNewCategoryModal($meta);
    	$meta = $this->customizeCategoriesField($meta);
        $meta = $this->createNewTagModal($meta);
        $meta = $this->customizeTagsField($meta);
        $meta = $this->createNewAuthorModal($meta);
        $meta = $this->customizeAuthorsField($meta);
        return $meta;
    }

    /**
     * @param array $meta
     * @return array
     */
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
                                            'handle' => 'blog_category_create',
                                            'buttons' => 1
                                        ]
                                    ),
									'autoRender'       => false,
									'ns'               => 'blog_new_category_form',
									'externalProvider' => 'blog_new_category_form.blog_new_category_form_data_source',
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
     * @param array $meta
     * @return array
     */
    protected function createNewTagModal(array $meta)
    {
        return $this->arrayManager->set(
            'create_tag_modal',
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isTemplate' => false,
                            'componentType' => 'modal',
                            'options' => [
                                'title' => __('New Tag')
                            ],
                            'imports' => [
                                'state' => '!index=create_tag:responseStatus'
                            ]
                        ]
                    ]
                ],
                'children' => [
                    'create_tag' => [
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
                                            'handle' => 'blog_tag_create',
                                            'buttons' => 1
                                        ]
                                    ),
                                    'autoRender'       => false,
                                    'ns'               => 'blog_new_tag_form',
                                    'externalProvider' => 'blog_new_tag_form.blog_new_tag_form_data_source',
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
     * @param array $meta
     * @return array
     */
    protected function createNewAuthorModal(array $meta)
    {
        return $this->arrayManager->set(
            'create_author_modal',
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isTemplate' => false,
                            'componentType' => 'modal',
                            'options' => [
                                'title' => __('New Author')
                            ],
                            'imports' => [
                                'state' => '!index=create_author:responseStatus'
                            ]
                        ]
                    ]
                ],
                'children' => [
                    'create_author' => [
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
                                            'handle' => 'blog_author_create',
                                            'buttons' => 1
                                        ]
                                    ),
                                    'autoRender'       => false,
                                    'ns'               => 'blog_new_author_form',
                                    'externalProvider' => 'blog_new_author_form.blog_new_author_form_data_source',
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
        $fieldCode = 'category_ids';

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
								'component'        => 'Magezon_Blog/js/components/new-option',
								'filterOptions'    => true,
								'chipsEnabled'     => true,
								'disableLabel'     => true,
								'levelsVisibility' => '1',
								'elementTmpl'      => 'ui/grid/filters/elements/ui-select',
								'options'          => $this->categoriesTree->toOptionArray(false),
        						'listens' => [
        							'index=create_category:responseData' => 'setParsed',
        							'newOption' => 'toggleOptionSelected'
        						],
        						'config' => [
        							'dataScope' => $fieldCode,
        							'sortOrder' => 10
        						]
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
										'targetName' => 'blog_post_form.blog_post_form.create_category_modal',
										'actionName' => 'toggleModal'
        							],
        							[
										'targetName' => 'blog_post_form.blog_post_form.create_category_modal.create_category',
										'actionName' => 'render'
        							],
        							[
										'targetName' => 'blog_post_form.blog_post_form.create_category_modal.create_category',
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

    /**
     * Customize categories field
     *
     * @param array $meta
     * @return array
     */
    protected function customizeTagsField($meta)
    {
        $fieldCode = 'tag_ids';

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
                                'label'            => __('Tags'),
                                'formElement'      => 'select',
                                'componentType'    => 'field',
                                'component'        => 'Magezon_Blog/js/components/new-option',
                                'filterOptions'    => true,
                                'chipsEnabled'     => true,
                                'disableLabel'     => true,
                                'levelsVisibility' => '1',
                                'elementTmpl'      => 'ui/grid/filters/elements/ui-select',
                                'options'          => $this->tagList->toOptionArray(),
                                'listens' => [
                                    'index=create_tag:responseData' => 'setParsed',
                                    'newOption' => 'toggleOptionSelected'
                                ],
                                'config' => [
                                    'dataScope' => $fieldCode,
                                    'sortOrder' => 10
                                ]
                            ]
                        ]
                    ]
                ],
                'create_tag_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title'             => __('New Tag'),
                                'formElement'       => 'container',
                                'additionalClasses' => 'admin__field-small',
                                'componentType'     => 'container',
                                'component'         => 'Magento_Ui/js/form/components/button',
                                'template'          => 'ui/form/components/button/container',
                                'actions'           => [
                                    [
                                        'targetName' => 'blog_post_form.blog_post_form.create_tag_modal',
                                        'actionName' => 'toggleModal'
                                    ],
                                    [
                                        'targetName' => 'blog_post_form.blog_post_form.create_tag_modal.create_tag',
                                        'actionName' => 'render'
                                    ],
                                    [
                                        'targetName' => 'blog_post_form.blog_post_form.create_tag_modal.create_tag',
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

    /**
     * @param $meta
     * @return array
     */
    protected function customizeAuthorsField($meta)
    {
        $fieldCode = 'author_id';

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
                                'label'            => __('Author'),
                                'formElement'      => 'select',
                                'componentType'    => 'field',
                                'component'        => 'Magezon_Blog/js/components/new-author',
                                'filterOptions'    => true,
                                'disableLabel'     => true,
                                'elementTmpl'      => 'ui/grid/filters/elements/ui-select',
                                'multiple'         => false,
                                'options'          => $this->authorList->toOptionArray(),
                                'listens' => [
                                    'index=create_author:responseData' => 'setParsed',
                                    'newOption' => 'toggleOptionSelected'
                                ],
                                'config' => [
                                    'dataScope' => $fieldCode,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'sortOrder' => 10,
                                ]
                            ]
                        ]
                    ]
                ],
                'create_category_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title'             => __('New Author'),
                                'formElement'       => 'container',
                                'additionalClasses' => 'admin__field-small',
                                'componentType'     => 'container',
                                'component'         => 'Magento_Ui/js/form/components/button',
                                'template'          => 'ui/form/components/button/container',
                                'actions'           => [
                                    [
                                        'targetName' => 'blog_post_form.blog_post_form.create_author_modal',
                                        'actionName' => 'toggleModal'
                                    ],
                                    [
                                        'targetName' => 'blog_post_form.blog_post_form.create_author_modal.create_author',
                                        'actionName' => 'render'
                                    ],
                                    [
                                        'targetName' => 'blog_post_form.blog_post_form.create_author_modal.create_author',
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
