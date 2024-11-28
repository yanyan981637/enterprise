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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutFactory;
use Magezon\Blog\Model\Category;
use Magezon\Blog\Model\ResourceModel\Category\CollectionFactory;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_Blog::category_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param CollectionFactory $categoryCollection
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        CollectionFactory $categoryCollection
    ) {
        parent::__construct($context);
        $this->dataPersistor     = $dataPersistor;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory     = $layoutFactory;
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * @param $identifier
     * @return mixed
     */
    function checkIssetIdentifier($identifier)
    {
        $collection = $this->categoryCollection->create();
        $categorys = $collection->addFieldToFilter('identifier', ['eq' => $identifier]);
        if ($categorys->count() > 0) {
            return $this->checkIssetIdentifier($identifier.'-copy');
        }
        return $identifier;
    }

    /**
     * @return Redirect|ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $hasError     = false;
        $data         = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (empty($data['category_id'])) {
            unset($data['category_id']);
        }

        if ($data) {

            /** @var Category $model */
            $model = $this->_objectManager->create(Category::class);
            $id    = $this->getRequest()->getParam('category_id');

            try {
                $model->load($id);
                if ($id && !$model->getId()) {
                    throw new LocalizedException(__('This category no longer exists.'));
                }

                if (isset($data['category_posts'])
                    && is_string($data['category_posts'])
                    && !$model->getPostsReadonly()
                ) {
                    $posts = json_decode($data['category_posts'], true);
                    $data['posted_posts'] = $posts;
                }

                $model->setData($data);
                $model->save();

                $this->messageManager->addSuccessMessage(__('You saved the category.'));
                $this->dataPersistor->clear('current_blog_category');

                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }

                if ($redirectBack === 'save_and_duplicate') {
                    $duplicate = $this->_objectManager->create(Category::class);
                    $identifierDup = $this->checkIssetIdentifier($data['identifier']);
                    $duplicate->setData($model->getData());
                    $duplicate->setCreatedAt(null);
                    $duplicate->setUpdatedAt(null);
                    $duplicate->setIdentifier($identifierDup);
                    $duplicate->setId(null);
                    $duplicate->save();
                    $this->messageManager->addSuccessMessage(__('You duplicated the category'));
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $duplicate->getId(), '_current' => true]);
                }

                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/*');
                }

                if (!$this->getRequest()->getPost('return_session_messages_only')) {
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId(), '_current' => true]);
                }
            } catch (LocalizedException $e) {
                $hasError = true;
                $this->messageManager->addExceptionMessage($e->getPrevious() ?:$e);
            } catch (\Exception $e) {
                $hasError = true;
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
            }

            if ($this->getRequest()->getPost('return_session_messages_only')) {
                $model->load($model->getId());
                // to obtain truncated category name
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));

                $data = $model->toArray();
                $data['name']   = $model->getTitle();
                $data['id']     = $model->getId();
                $data['parent'] = (int)$model->getParentId();
                $data['level']  = 0;

                /** @var Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData(
                    [
                        'messages' => $block->getGroupedHtml(),
                        'error'    => $hasError,
                        'item'     => $data
                    ]
                );
            }

            $this->dataPersistor->set('current_blog_category', $data);
            return $resultRedirect->setPath('*/*/edit', ['category_id' => $this->getRequest()->getParam('category_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
