<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.1.8
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Ui\ScoreRule\Source;

use Magento\Framework\Option\ArrayInterface;

class ScoreFactorRelatively implements ArrayInterface
{
    const RELATIVELY_SCORE      = 'score';
    const RELATIVELY_POPULARITY = 'popularity';
    const RELATIVELY_RATING     = 'rating';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::RELATIVELY_SCORE,
                'label' => __('initial score'),
            ],
            [
                'value' => self::RELATIVELY_POPULARITY,
                'label' => __('product popularity (orders)'),
            ],
            [
                'value' => self::RELATIVELY_RATING,
                'label' => __('product rating (reviews)'),
            ],
        ];
    }
}
