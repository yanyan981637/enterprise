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

namespace Magezon\Blog\Controller\Adminhtml\Author;

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
use Magezon\Blog\Model\Author;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_Blog::author_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param LayoutFactory $layoutFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->layoutFactory     = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
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
        if (empty($data['author_id'])) {
            unset($data['author_id']);
        }
        if ($data) {
            /** @var Author $model */
            $model = $this->_objectManager->create(Author::class);
            $id    = $this->getRequest()->getParam('author_id');

            try {
                $model->load($id);
                if ($id && !$model->getId()) {
                    throw new LocalizedException(__('This author no longer exists.'));
                }

                if (isset($data['author_posts'])
                    && is_string($data['author_posts'])
                    && !$model->getPostsReadonly()
                ) {
                    $posts = json_decode($data['author_posts'], true);
                    $data['posted_posts'] = $posts;
                }

                $model->setData($data);
                $model->save();

                $this->messageManager->addSuccessMessage(__('You saved the author.'));
                $this->dataPersistor->clear('current_author');

                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }

                if ($redirectBack === 'save_and_duplicate') {
                    $duplicate = $this->_objectManager->create(Author::class);
                    $duplicate->setData($model->getData());
                    $duplicate->setCreatedAt(null);
                    $duplicate->setUpdatedAt(null);
                    $duplicate->setId(null);
                    $duplicate->save();
                    $this->messageManager->addSuccessMessage(__('You duplicated the author'));
                    return $resultRedirect->setPath('*/*/edit', ['author_id' => $duplicate->getId(), '_current' => true]);
                }

                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/*');
                }
                if (!$this->getRequest()->getPost('return_session_messages_only')) {
                    return $resultRedirect->setPath('*/*/edit', ['author_id' => $model->getId(), '_current' => true]);
                }

//                return $resultRedirect->setPath('*/*/edit', ['author_id' => $model->getId(), '_current' => true]);
            } catch (LocalizedException $e) {
                $hasError     = true;
                $this->messageManager->addExceptionMessage($e->getPrevious() ?:$e);
            } catch (\Exception $e) {
                $hasError     = true;
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the author.'));
            }

            if ($this->getRequest()->getPost('return_session_messages_only')) {
                $model->load($model->getId());
                // to obtain truncated category name
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));

                $data = $model->toArray();
                $data['name']   = $model->getTitle();
                $data['id']     = $model->getId();

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

            $this->dataPersistor->set('current_author', $data);
            return $resultRedirect->setPath('*/*/edit', ['author_id' => $this->getRequest()->getParam('author_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
