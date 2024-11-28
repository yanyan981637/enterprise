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

namespace Magezon\Blog\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
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
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
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
                    throw new LocalizedException(__('Your profile no longer exists.'));
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

                $this->messageManager->addSuccessMessage(__('You saved your profile.'));
                $this->dataPersistor->clear('current_author');

                return $resultRedirect->setPath('*/*/*');
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?:$e);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving your profile.'));
            }

            $this->dataPersistor->set('current_author', $data);
            return $resultRedirect->setPath('*/*/edit', ['author_id' => $this->getRequest()->getParam('author_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
