<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Controller\Adminhtml\Icon;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magezon\ProductAttachments\Model\Icon;
use Magezon\ProductAttachments\Model\ResourceModel\Icon\CollectionFactory as IconCollectionFactory;

class MassEnable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::icon';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var IconCollectionFactory
     */
    protected $iconCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param IconCollectionFactory $iconCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        IconCollectionFactory $iconCollectionFactory
    ) {
        $this->filter = $filter;
        $this->iconCollectionFactory = $iconCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $iconCollection = $this->iconCollectionFactory->create();
        $collection = $this->filter->getCollection($iconCollection);
        $collectionSize = $collection->getSize();

        foreach ($collection as $icon) {
            $icon->setIsActive(Icon::STATUS_ENABLED)->save();
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been enabled.', $collectionSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
