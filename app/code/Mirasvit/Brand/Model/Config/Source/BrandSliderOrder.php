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

namespace Mirasvit\Brand\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BrandSliderOrder implements ArrayInterface
{
    const SLIDER_TITLE_ORDER = 0;
    const SLIDER_POSITION_ORDER = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::SLIDER_TITLE_ORDER, 'label' => __('Title')],
            ['value' => self::SLIDER_POSITION_ORDER, 'label' => __('Position')],
        ];

        return $options;
    }
}
