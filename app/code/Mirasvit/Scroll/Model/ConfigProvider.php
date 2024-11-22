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

namespace Mirasvit\Scroll\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Scroll\Model\Config\Source\ModeSource;

class ConfigProvider
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get selector of blocks to which apply the infinity scroll widget.
     */
    public function getProductListSelector(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_scroll/general/product_list_selector',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMode(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_scroll/general/mode',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getLoadPrevText(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_scroll/general/prev_text',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getLoadNextText(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_scroll/general/next_text',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnabled(): bool
    {
        return in_array(
            $this->getMode(),
            [ModeSource::MODE_BUTTON, ModeSource::MODE_INFINITE, ModeSource::MODE_BUTTON_INFINITE, ModeSource::MODE_INFINITE_BUTTON],
            true
        );
    }

    public function getPageLimit(): int
    {
        return (int)$this->scopeConfig->getValue('mst_scroll/general/page_limit', ScopeInterface::SCOPE_STORE);
    }

    public function isProgressBarEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'mst_scroll/general/progress_bar_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getProgressBarLabel(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_scroll/general/progress_bar_label',
            ScopeInterface::SCOPE_STORE
        );
    }
}
