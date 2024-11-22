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
use Mirasvit\Report\Service\StateService;

class State extends AbstractApi
{
    /**
     * @var StateService
     */
    private $stateService;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * State constructor.
     * @param StateService $stateService
     * @param Context $context
     */
    public function __construct(
        StateService $stateService,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        Context $context
    ) {
        $this->stateService = $stateService;
        $this->serializer = $serializer;

        parent::__construct($context, $serializer);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $request = $this->getRequest();

        $namespace = $this->getRequest()->getParam('identifier');

        $state = [
            'columns'    => $request->getParam('columns'),
            'dimensions' => $request->getParam('dimensions'),
            'sortOrders' => $request->getParam('sortOrders'),
            'filters'    => $request->getParam('filters'),
            'pageSize'   => $request->getParam('pageSize'),
        ];

        $this->stateService->saveState($namespace, $state);

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->representJson($this->serializer->serialize(
            ['success' => true]
        ));
    }
}
