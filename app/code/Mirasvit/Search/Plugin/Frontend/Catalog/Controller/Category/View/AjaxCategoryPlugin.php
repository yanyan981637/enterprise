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


declare(strict_types=1);

namespace Mirasvit\Search\Plugin\Frontend\Catalog\Controller\Category\View;

use Magento\Framework\App\RequestInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Service\AjaxResponseService;

/**
 * @see \Magento\Catalog\Controller\Category\View::execute()
 */
class AjaxCategoryPlugin
{

    private $configProvider;

    private $ajaxResponseService;

    private $request;

    public function __construct(
        ConfigProvider $configProvider,
        AjaxResponseService $ajaxResponseService,
        RequestInterface $request
    ) {
        $this->configProvider      = $configProvider;
        $this->ajaxResponseService = $ajaxResponseService;
        $this->request             = $request;
    }

    /**
     * @param \Magento\Catalog\Controller\Category\View $subject
     * @param \Magento\Framework\View\Result\Page       $page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function afterExecute($subject, $page)
    {
        if (
            $this->configProvider->isCategorySearch()
            && $this->request->isAjax()
            && !$this->request->getParam('is_scroll')
        ) {
            return $this->ajaxResponseService->getAjaxResponse($page);
        }

        return $page;
    }
}
