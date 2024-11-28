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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magezon\ProductAttachments\Model\CategoryFactory;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::category_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;
    
    private $layoutFactory;

    private $resultJsonFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory     = $layoutFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $hasError     = false;
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $model = $this->categoryFactory->create();
                $redirectBack = $this->getRequest()->getParam('back', false);
                $id = $this->getRequest()->getParam('category_id');
                if ($id) {
                    $model->setId($data['category_id']);
                    if (!$model->getId()) {
                        throw new LocalizedException(__('This category no longer exists.'));
                    }
                }
                $model->setData($data);
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the category.'));
                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }
                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/');
                }
                if (!$this->getRequest()->getPost('return_session_messages_only')) {
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId(), '_current' => true]);
                }
            } catch (LocalizedException $e) {
                $hasError = true;
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $hasError = true;
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
            }
            if ($this->getRequest()->getPost('return_session_messages_only')) {

                /** @var $block \Magento\Framework\View\Element\Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));

                $data = $model->toArray();
                $data['name']   = $model->getName();
                $data['category_id']     = $model->getId();

                /** @var \Magento\Framework\Controller\Result\Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData(
                    [
                        'messages' => $block->getGroupedHtml(),
                        'error'    => $hasError,
                        'item'     => $data
                    ]
                );
            }

            $this->dataPersistor->set('productattachments_category', $data);
            return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId()]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
