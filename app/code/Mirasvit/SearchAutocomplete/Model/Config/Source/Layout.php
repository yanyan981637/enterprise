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



namespace Mirasvit\SearchAutocomplete\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\SearchAutocomplete\Model\ConfigProvider;

class Layout implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => ConfigProvider::LAYOUT_1_COLUMN,
                'label' => __('1 Column'),
            ],
            [
                'value' => ConfigProvider::LAYOUT_2_COLUMNS,
                'label' => __('2 Columns'),
            ],
            [
                'value' => ConfigProvider::LAYOUT_IN_PAGE,
                'label' => __('Full Size'),
            ],
        ];
    }
}
