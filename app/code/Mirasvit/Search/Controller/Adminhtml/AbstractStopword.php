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



namespace Mirasvit\Search\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Repository\StopwordRepository;
use Mirasvit\Search\Service\StopwordService;
use Magento\Framework\Filesystem\DirectoryList;

abstract class AbstractStopword extends Action
{
    protected $stopwordRepository;

    protected $stopwordService;

    protected $directoryList;

    protected $context;

    public function __construct(
        StopwordRepository  $stopwordRepository,
        StopwordService     $stopwordService,
        DirectoryList $directoryList,
        Context $context
    ) {
        $this->stopwordRepository = $stopwordRepository;
        $this->stopwordService    = $stopwordService;
        $this->directoryList      = $directoryList;
        $this->context            = $context;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Search::search');

        $resultPage->getConfig()->getTitle()->prepend((string)__('Manage Stopwords'));

        return $resultPage;
    }

    /**
     * @return StopwordInterface
     */
    protected function initModel()
    {
        $model = $this->stopwordRepository->create();

        if ($this->getRequest()->getParam(StopwordInterface::ID)) {
            $model = $this->stopwordRepository->get((int) $this->getRequest()->getParam(StopwordInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Search::search_stopword');
    }
}
