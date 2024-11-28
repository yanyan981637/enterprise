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

namespace Magezon\ProductAttachments\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magezon\ProductAttachments\Model\Category;
use Magezon\ProductAttachments\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class MassDisable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::category';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->filter = $filter;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $collection = $this->filter->getCollection($categoryCollection);
        $collectionSize = $collection->getSize();

        foreach ($collection as $category) {
            $category->setIsActive(Category::STATUS_DISABLE)->save();
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been disabled.', $collectionSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
