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
 * @package   mirasvit/module-report
 * @version   1.4.13
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;

class Request extends AbstractApi
{
    /**
     * @var RequestBuilderInterface
     */
    private $requestBuilder;

    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Request constructor.
     * @param RequestBuilderInterface $requestBuilder
     * @param ReportRepositoryInterface $reportRepository
     * @param Context $context
     */
    public function __construct(
        RequestBuilderInterface $requestBuilder,
        ReportRepositoryInterface $reportRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        Context $context
    ) {
        $this->requestBuilder   = $requestBuilder;
        $this->reportRepository = $reportRepository;
        $this->serializer = $serializer;

        parent::__construct($context, $serializer);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $r = $this->getRequest();

        $request = $this->requestBuilder->create();

        $request->setTable($r->getParam('table'));

        $request->setDimensions($r->getParam('dimensions'));

        foreach ($r->getParam('columns', []) as $c) {
            $request->addColumn($c);
        }

        $request->setPageSize($r->getParam('pageSize', 20));

        $request->setCurrentPage($r->getParam('currentPage', 1));

        foreach ($r->getParam('filters', []) as $filter) {
            if (!isset($filter['column'])) {
                continue;
            }

            if ($filter['conditionType'] == 'like' || $filter['conditionType'] == 'nlike')  {
                $filter['value'] = '%' . $filter['value'] . '%';
            }

            $request->addFilter($filter['column'], $filter['value'], $filter['conditionType']);
        }

        foreach ($r->getParam('sortOrders', []) as $sortOrder) {
            $request->addSortOrder($sortOrder['column'], $sortOrder['direction']);
        }


        /** @var \Magento\Framework\App\Response\Http $jsonResponse */
        $jsonResponse = $this->getResponse();

        try {
            if ($r->getParam('identifier')) {
                $report = $this->reportRepository->get($r->getParam('identifier'));

                if (method_exists($report, 'modifyRequest')) {
                    $request = $report->modifyRequest($request);
                }

                $response = $request->process();

                foreach ($response->getItems() as $item) {
                    $actions = $report->getActions($item, $request);
                    $item->setFormattedData('actions', $actions);
                }
            } else {
                $response = $request->process();
            }

            $responseData = $response->toArray();

            $jsonResponse->representJson($this->serializer->serialize([
                'success' => true,
                'data'    => $responseData,
            ]));
        } catch (\Exception $e) {
            $jsonResponse->representJson($this->serializer->serialize([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function _processUrlKeys()
    {
        return true;
    }
}
