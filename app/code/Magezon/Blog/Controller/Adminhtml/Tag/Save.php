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

namespace Magezon\Blog\Controller\Adminhtml\Tag;

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
use Magezon\Blog\Model\Tag;
use Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_Blog::tag_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var CollectionFactory
     */
    protected $tagCollection;


    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @param $identifier
     * @return string
     */
    function checkIssetIdentifier($identifier)
    {
        $collection = $this->tagCollection->create();
        $tags = $collection->addFieldToFilter('identifier', ['eq' => $identifier]);
        if ($tags->count() > 0) {
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
        if (empty($data['tag_id'])) {
            unset($data['tag_id']);
        }
        if ($data) {
            /** @var Tag $model */
            $model = $this->_objectManager->create(Tag::class);
            $id    = $this->getRequest()->getParam('tag_id');

            try {
                $model->load($id);
                if ($id && !$model->getId()) {
                    throw new LocalizedException(__('This tag no longer exists.'));
                }

                if (isset($data['tag_posts'])
                    && is_string($data['tag_posts'])
                    && !$model->getPostsReadonly()
                ) {
                    $posts = json_decode($data['tag_posts'], true);
                    $data['posted_posts'] = $posts;
                }

                $model->setData($data);
                $model->save();

                $this->messageManager->addSuccessMessage(__('You saved the tag.'));
                $this->dataPersistor->clear('current_tag');

                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }

                if ($redirectBack === 'save_and_duplicate') {
                    $duplicate = $this->_objectManager->create(Tag::class);

                    $identifierDup = $this->checkIssetIdentifier($data['identifier']);

                    $duplicate->setData($model->getData());
                    $duplicate->setCreatedAt(null);
                    $duplicate->setUpdatedAt(null);
                    $duplicate->setIdentifier($identifierDup);
                    $duplicate->setId(null);
                    $duplicate->save();
                    $this->messageManager->addSuccessMessage(__('You duplicated the tag'));
                    return $resultRedirect->setPath('*/*/edit', ['tag_id' => $duplicate->getId(), '_current' => true]);
                }

                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/*');
                }

                if (!$this->getRequest()->getPost('return_session_messages_only')) {
                    return $resultRedirect->setPath('*/*/edit', ['tag_id' => $model->getId(), '_current' => true]);
                }
            } catch (LocalizedException $e) {
                $hasError     = false;
                $this->messageManager->addExceptionMessage($e->getPrevious() ?:$e);
            } catch (\Exception $e) {
                $hasError = true;
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the tag.'));
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

            $this->dataPersistor->set('current_tag', $data);
            return $resultRedirect->setPath('*/*/edit', ['tag_id' => $this->getRequest()->getParam('tag_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
