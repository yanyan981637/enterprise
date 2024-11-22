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

namespace Mirasvit\Scroll\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Mirasvit\Scroll\Model\ConfigProvider;

class Scroll extends Template
{
    private $configProvider;

    public function __construct(
        ConfigProvider $config,
        Context        $context,
        array          $data = []
    ) {
        $this->configProvider = $config;

        parent::__construct($context, $data);
    }

    public function getPager(): ?Pager
    {
        return $this->getLayout()->getBlock('product_list_toolbar_pager') ? : null;
    }

    public function getJsConfig(): array
    {
        $pager = $this->getPager();
        if (!$pager || !$pager->getCollection()) {
            return [];
        }

        $currentPage = (int)$pager->getCurrentPage();

        $prevText = $this->configProvider->getLoadPrevText();
        $nextText = $this->configProvider->getLoadNextText();

        return [
            'mode'                => $this->configProvider->getMode(),
            'pageLimit'           => $this->configProvider->getPageLimit(),
            'pageNum'             => $currentPage,
            'initPageNum'         => $currentPage,
            'prevPageNum'         => $currentPage === 1 ? false : $currentPage - 1,
            'nextPageNum'         => $currentPage === (int)$pager->getLastPageNum() ? false : $currentPage + 1,
            'lastPageNum'         => $pager->getLastPageNum(),
            'loadPrevText'        => (string)__($prevText),
            'loadNextText'        => (string)__($nextText),
            'itemsTotal'          => (int)$pager->getCollection()->getSize(),
            'itemsLimit'          => (int)$pager->getLimit(),
            'progressBarEnabled'  => $this->configProvider->isProgressBarEnabled(),
            'progressBarText'     => $this->configProvider->getProgressBarLabel(),
            'productListSelector' => $this->configProvider->getProductListSelector()
        ];
    }

    public function getInitConfig(): ?array
    {
        $jsConfig = $this->getJsConfig();

        if (empty($jsConfig)) {
            return null;
        }

        return [
            $this->configProvider->getProductListSelector() => [
                'Mirasvit_Scroll/js/scroll' => $jsConfig,
            ],
        ];
    }

    public function isEnabled(): bool
    {
        return $this->configProvider->isEnabled() && $this->configProvider->getProductListSelector();
    }
}
