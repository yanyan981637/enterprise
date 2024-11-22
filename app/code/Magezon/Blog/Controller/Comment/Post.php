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

namespace Magezon\Blog\Controller\Comment;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\CommentFactory;
use Magezon\Blog\Model\Email;
use Magezon\Blog\Model\PostFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Cache\TypeListInterface;

class Post extends Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CommentFactory
     */
    protected $commentFactory;

    /**
     * @var Email
     */
    protected $email;


    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param RemoteAddress $remoteAddress
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param TypeListInterface $cacheTypeList
     * @param JsonFactory $resultJsonFactory
     * @param PostFactory $postFactory
     * @param Data $dataHelper
     * @param CommentFactory $commentFactory
     * @param Email $email
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        RemoteAddress $remoteAddress,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        TypeListInterface $cacheTypeList,
        JsonFactory $resultJsonFactory,
        PostFactory $postFactory,
        Data $dataHelper,
        CommentFactory $commentFactory,
        Email $email
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->remoteAddress = $remoteAddress;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->cacheTypeList = $cacheTypeList;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->postFactory = $postFactory;
        $this->dataHelper = $dataHelper;
        $this->commentFactory = $commentFactory;
        $this->email = $email;
    }

    /**
     * @return ResponseInterface|Json|Redirect|ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) return $this->resultRedirectFactory->create()->setPath('');
        try {
            $commentApproved = $this->dataHelper->getConfig('post_page/comments/need_approve');
            $this->validatedParams();
//            $this->validatedRecaptcha();
            $this->dataPersistor->clear('blog_comment_form');
            $post = $this->getRequest()->getPostValue();
            $data['author'] = trim(strip_tags($post['author']));
            $data['author_email'] = trim($post['author_email']);
            $data['post_id'] = (int)$post['post_id'];
            $data['content'] = trim(strip_tags($post['content']));
            if (isset($post['parent_id'])) $data['parent_id'] = (int)$post['parent_id'];
            $post['customer_id'] = $this->customerSession->getId();
            $post['store_id'] = $this->storeManager->getStore()->getId();
            $post['status'] = $this->dataHelper->getDefaultCommentStatus();
            $post['remote_ip'] = $this->remoteAddress->getRemoteAddress();
            $post['brower'] = $this->getRequest()->getServer('HTTP_USER_AGENT');

            if ($this->dataHelper->isEnabledSendEmail()) {
                $postRecord = $this->postFactory->create()->load($data['post_id']);
                $author = $postRecord->getAuthor();
                $email = $author->getEmail();
                $name = $author->getFullName();
                $commentDetail = $data['content'];
                $postLink = $this->getRequest()->getServer('HTTP_REFERER');

                if ((isset($email) && !$post['parent_id'])
                    || ($this->dataHelper->isEnabledEmailReplyToAdmin() && $post['parent_id'])
                ) {
                    $this->email->sendEmail(
                        $email,
                        $name,
                        $post['author_email'],
                        $commentDetail,
                        $postLink,
                        1
                    );
                }

                if ($post['parent_id'] != 0) {
                    $postCommentParent = $this->commentFactory->create()->load($post['parent_id']);
                    if ($postCommentParent->getAuthorEmail() != $post['author_email']) {
                        $this->email->sendEmail(
                            $postCommentParent->getAuthorEmail(),
                            $post['author'],
                            $post['author_email'],
                            $commentDetail,
                            $postLink,
                            2
                        );
                    }
                }
            }

            $createdAtFormatted = $this->commentFactory->create()->getCreatedAtFormatted();
            $imageUrl = $this->commentFactory->create()->getImageUrl();
            $comment = $this->commentFactory->create();
            $comment->setData($post);
            $comment->save();

            $_types = 'full_page';
            $this->cacheTypeList->cleanType($_types);

            $this->messageManager->addSuccessMessage(
                __('Your comment has been successfully submitted.')
            );

            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData([
                    'comment_info' => $comment->getData(),
                    'image' => $comment->getImageUrl(),
                    'date' => $comment->getCreatedAtFormatted(),
                    'comment_approve' => $commentApproved
                ]
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('blog_comment_form', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('blog_comment_form', $this->getRequest()->getParams());
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('author')) === '') {
            throw new LocalizedException(__('Enter the name and try again.'));
        }
        if (trim($request->getParam('content')) === '') {
            throw new LocalizedException(__('Enter the content and try again.'));
        }
        if (false === \strpos($request->getParam('author_email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (!$request->getParam('post_id')) {
            throw new \Exception(__('Not found Post ID'));
        }
    }

    /**
     * @return boolean
     */
    private function validatedRecaptcha()
    {
        $post      = $this->getRequest()->getPostValue();
        $secretKey = $this->dataHelper->getConfig('post_page/comments/recaptcha/secret_key');
        $remoteIp  = $this->remoteAddress->getRemoteAddress();
        if (isset($post['g-recaptcha-response'])) {
            $postData = http_build_query([
                'secret'   => $secretKey,
                'response' => $post['g-recaptcha-response'],
                'remoteip' => $remoteIp
            ]);
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postData
                ]
            ];
            $context  = stream_context_create($opts);
            $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $result   = json_decode($response);
            if (!$result->success) {
                throw new LocalizedException(__('Incorrect CAPTCHA.'));
            }
        }
    }
}
