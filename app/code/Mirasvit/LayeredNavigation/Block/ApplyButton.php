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

namespace Mirasvit\LayeredNavigation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\LayeredNavigation\Model\Config\Source\FilterApplyingModeSource;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;

class ApplyButton extends Template
{
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;

        parent::__construct($context, $data);
    }

    public function isApplyingMode(): bool
    {
        return $this->configProvider->isAjaxEnabled()
            && (
                $this->configProvider->getApplyingMode() == FilterApplyingModeSource::OPTION_BY_BUTTON_CLICK
                || (
                    $this->configProvider->getApplyingMode() == FilterApplyingModeSource::OPTION_INSTANTLY
                    && $this->configProvider->getIsConfirmOnMobile()
                )
            );
    }
}
