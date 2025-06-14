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



namespace Mirasvit\SearchLanding\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Repository\PageRepository;

abstract class Page extends Action
{
    protected $pageRepository;

    protected $context;

    public function __construct(
        PageRepository $pageRepository,
        Context $context
    ) {
        $this->pageRepository = $pageRepository;
        $this->context        = $context;

        parent::__construct($context);
    }

    public function initModel(): PageInterface
    {
        $model = $this->pageRepository->create();

        if ($this->getRequest()->getParam(PageInterface::ID)) {
            $model = $this->pageRepository->get((int)$this->getRequest()->getParam(PageInterface::ID));
        }

        return $model;
    }

    protected function initPage(ResultPage $resultPage): ResultPage
    {
        $resultPage->setActiveMenu('Magento_Backend::system');
        $resultPage->getConfig()->getTitle()->prepend((string)__('Search'));
        $resultPage->getConfig()->getTitle()->prepend((string)__('Landing Pages'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchLanding::search_landing_page');
    }
}
