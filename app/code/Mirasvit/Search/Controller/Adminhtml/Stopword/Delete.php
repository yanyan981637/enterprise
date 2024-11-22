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



namespace Mirasvit\Search\Controller\Adminhtml\Stopword;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Controller\Adminhtml\AbstractStopword;
use Mirasvit\Search\Repository\StopwordRepository;
use Mirasvit\Search\Service\StopwordService;
use Magento\Framework\Filesystem\DirectoryList;

class Delete extends AbstractStopword
{
    private $filter;

    public function __construct(
        Filter $filter,
        StopwordRepository $stopwordRepository,
        StopwordService    $stopwordService,
        DirectoryList $directoryList,
        Context $context
    ) {
        $this->filter = $filter;

        parent::__construct($stopwordRepository, $stopwordService, $directoryList, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $ids = [];

        if ($this->getRequest()->getParam(StopwordInterface::ID)) {
            $ids = [(int) $this->getRequest()->getParam(StopwordInterface::ID)];
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)
            || $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)
        ) {
            $ids = $this->filter->getCollection($this->stopwordRepository->getCollection())->getAllIds();
        }

        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $page = $this->stopwordRepository->get((int) $id);
                    $this->stopwordRepository->delete($page);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->messageManager->addSuccessMessage(
                (string)__('%1 item(s) was removed', count($ids))
            );
        } else {
            $this->messageManager->addErrorMessage((string)__('Please select item(s)'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
