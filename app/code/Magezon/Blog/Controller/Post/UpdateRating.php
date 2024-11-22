<?php

namespace Magezon\Blog\Controller\Post;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magezon\Blog\Model\PostFactory;

class UpdateRating extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PostFactory $postFactory
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PostFactory $postFactory,
        TypeListInterface $cacheTypeList
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->postFactory = $postFactory;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('');
        }
        try {
            $data = $this->getRequest()->getPostValue();
            $post = $this->postFactory->create()->load($data['postId']);
            $likeTotal = $post->getLikeTotal();
            $dislikeTotal = $post->getDislikeTotal();

            if(isset($data['isLikeClick'])) {
                if(!$data['isLiked']) {
                    $likeTotal = $likeTotal + 1;
                    if($data['isDisliked'] !== 'false') {
                        $dislikeTotal -= 1;
                    }

                }else{
                    $likeTotal -= 1;
                }
            }

            if(isset($data['isDislikeClick'])) {
                if(!$data['isDisliked']) {
                    $dislikeTotal += 1;
                    if($data['isLiked'] !== 'false') {
                        $likeTotal -= 1;
                    }

                }else{
                    $dislikeTotal -= 1;
                }
            }

            $data['likeTotal'] = $likeTotal;
            $data['disLikeTotal'] = $dislikeTotal;
            $post->setLikeTotal($likeTotal);
            $post->setDislikeTotal($dislikeTotal);
            $post->save();

            $response = ['code' => 200, 'data' => $data];
            $this->cacheTypeList->cleanType('full_page');
        } catch (Exception $e) {
            $response = ['code' => 500, 'message' => $e->getMessage()];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
