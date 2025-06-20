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

namespace Mirasvit\Brand\Ui\BrandPage\Form\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Brand\Model\Config\BrandPageConfig;

class BannerPosition implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $data = [
            BrandPageConfig::BANNER_AFTER_TITLE_POSITION        => BrandPageConfig::BANNER_AFTER_TITLE_POSITION,
            BrandPageConfig::BANNER_BEFORE_DESCRIPTION_POSITION => BrandPageConfig::BANNER_BEFORE_DESCRIPTION_POSITION,
            BrandPageConfig::BANNER_AFTER_DESCRIPTION_POSITION  => BrandPageConfig::BANNER_AFTER_DESCRIPTION_POSITION,
        ];

        $options = [];
        foreach ($data as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        $this->options = $options;

        return $this->options;
    }
}
