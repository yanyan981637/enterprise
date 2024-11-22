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

namespace Magezon\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magezon\Blog\Model\Post;
use Magento\Framework\Event\ManagerInterface;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_Blog::post_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ManagerInterface $eventManager
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->eventManager = $eventManager;
        parent::__construct($context);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (empty($data['post_id'])) {
            unset($data['post_id']);
        }
        if ($data) {
            /** @var Post $model */
            $model = $this->_objectManager->create(Post::class);
            $id = $this->getRequest()->getParam('post_id');
            try {
                $model->load($id);
                if ($id && !$model->getId()) {
                    throw new LocalizedException(__('This post no longer exists.'));
                }

                if (isset($data['post_products'])
                    && is_string($data['post_products'])
                    && !$model->getProductsReadonly()
                ) {
                    $posts = json_decode($data['post_products'], true);
                    $data['posted_products'] = $posts;
                }

                if (isset($data['post_posts'])
                    && is_string($data['post_posts'])
                    && !$model->getPostsReadonly()
                ) {
                    $posts = json_decode($data['post_posts'], true);
                    $data['posted_posts'] = $posts;
                }
                if(empty($data['read_time'])){
                    $data['read_time'] = null;
                }
                if(empty($data['end_date'])){
                    $data['end_date'] = null;
                }

                if(isset($data['category_ids']) && !$model->issetCategorires($data['category_ids'])) {
                    $data['category_ids'] = [];
                }
                if(isset($data['tag_ids']) && !$model->issetTags($data['tag_ids'])) {
                    $data['tag_ids'] = [];
                }
                if(isset($data['author_id']) && !$model->issetAuthor($data['author_id'])) {
                    unset($data['author_id']);
                }

                if(!isset($data['category_ids'])) {
                    $data['category_ids'] = [];
                }

                if(!isset($data['tag_ids'])) {
                    $data['tag_ids'] = [];
                }

                $originData = $model->getData();
                $model->setData($data);
                $model->save();
                $this->dataPersistor->clear('current_post');
                if ($redirectBack === 'save_and_duplicate') {
                    $duplicate = $this->_objectManager->create(Post::class);
                    $duplicate->setData($model->getData());
                    $duplicate->setData('products_position', []);
                    $duplicate->setData('posted_products', $model->getPostedProducts());
                    $duplicate->setData('post_posts', $model->getPostsPosition());
                    $duplicate->setData('posts_position', []);
                    $duplicate->setCreatedAt(null);
                    $duplicate->setLikeTotal(0);
                    $duplicate->setDislikeTotal(0);
                    $duplicate->setTotalViews(0);
                    $duplicate->setUpdatedAt(null);
                    $duplicate->setId(null);
                    $duplicate->setIdentifier($model->getIdentifier() . '-' . rand());
                    $duplicate->save();
                    $this->messageManager->addSuccessMessage(__('You duplicated the post'));
                    return $resultRedirect->setPath('*/*/edit', ['post_id' => $duplicate->getId(), '_current' => true]);
                }
                $this->messageManager->addSuccessMessage(__('You saved the post.'));
                $this->eventManager->dispatch(
                    'after_save_post_success',
                    ['data' => $data, 'origin_data' => $originData]
                );

                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }

                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/*');
                }

                return $resultRedirect->setPath('*/*/edit', ['post_id' => $model->getId(), '_current' => true]);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the post.'));
            }

            $this->dataPersistor->set('current_post', $data);
            return $resultRedirect->setPath('*/*/edit', ['post_id' => $this->getRequest()->getParam('post_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
