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
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PriceOptions implements ArrayInterface
{
    const OPTION_DEFAULT       = 0;
    const OPTION_SLIDER        = 1;
    const OPTION_SLIDER_BUTTON = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::OPTION_DEFAULT, 'label' => __('Disabled')],
            ['value' => self::OPTION_SLIDER, 'label' => __('Slider')],
        ];

        return $options;
    }
}
