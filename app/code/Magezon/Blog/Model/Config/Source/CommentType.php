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

namespace Magezon\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magezon\Blog\Model\Comment;

class CommentType implements ArrayInterface
{
    /**
     * @var Comment
     */
    protected $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(
        Comment $comment
    )
    {
        $this->comment = $comment;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $types   = $this->comment->getCommentTypes();
        $options[] = [
            'label' => __('Disable Completely'),
            'value' => ''
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
