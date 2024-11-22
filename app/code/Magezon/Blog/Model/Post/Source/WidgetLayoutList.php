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

namespace Magezon\Blog\Model\Post\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magezon\Blog\Model\Post;

class WidgetLayoutList implements OptionSourceInterface
{
    /**
     * @var Post
     */
    protected $blogPost;

    /**
     * @param Post $blogPost
     */
    public function __construct(
        Post $blogPost
    )
    {
        $this->blogPost = $blogPost;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = $this->blogPost->getLayouts();
        $options = [];
        $options[] = [
            'label' => __('Slider'),
            'value' => 'slider'
        ];
        foreach ($types as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key
            ];
        }
        return $options;
    }
}
