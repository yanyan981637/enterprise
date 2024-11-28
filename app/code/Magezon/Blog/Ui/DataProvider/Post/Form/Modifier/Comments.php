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

use Magento\Framework\Registry;
use Magento\Ui\Component\Form;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\Comment;

class Comments implements ModifierInterface
{
    /**
     * Group order
     */
    const GROUP_ORDER = 'comments';

    /**
     * sort order
     */
    const SORT_ORDER  = 2000;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param Registry $registry
     * @param Data $dataHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Registry $registry,
        Data $dataHelper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registry   = $registry;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentPost()
    {
        return $this->registry->registry('current_post');
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $commentType = $this->dataHelper->getCommentType();
        if (!$this->getCurrentPost()->getId() || ($commentType != Comment::TYPE_NATIVE)) return $meta;

        $meta[static::GROUP_ORDER] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Comments'),
                        'collapsible'   => true,
                        'opened'        => false,
                        'componentType' => Form\Fieldset::NAME,
                        'sortOrder'     => 1000
                    ]
                ]
            ],
            'children' => [
                'blog_comment_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender'         => true,
                                'componentType'      => 'insertListing',
                                'dataScope'          => 'blog_comment_listing',
                                'externalProvider'   => 'blog_comment_listing.blog_comment_listing_data_source',
                                'selectionsProvider' => 'blog_comment_listing.blog_comment_listing.blog_comment_columns.ids',
                                'ns'                 => 'blog_comment_listing',
                                'render_url'         => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink'       => false,
                                'behaviourType'      => 'simple',
                                'externalFilterMode' => false,
                                'imports'            => [
                                    'postId' => '${ $.provider }:data.current_post_id',
                                    '__disableTmpl' => ['postId' => false],
                                ],
                                'exports' => [
                                    'postId' => '${ $.externalProvider }:params.current_post_id',
                                    '__disableTmpl' => ['postId' => false],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $data['current_post_id'] = $this->getCurrentPost()->getId();
        return $data;
    }
}
