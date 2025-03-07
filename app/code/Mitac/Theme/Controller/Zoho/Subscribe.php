<?php

namespace Mitac\Theme\Controller\Zoho;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Subscribe extends Action implements HttpPostActionInterface
{
    /**
     * @var FormKeyValidator
     * */
    protected $formKeyValidator;

    /**
     * @var JsonFactory
     */
    protected $resultJson;

    public function __construct(
        Context $context,
        JsonFactory $resultJson,
        FormKeyValidator $formKeyValidator,
    ){
        parent::__construct($context);
        $this->resultJson = $resultJson;
        $this->formKeyValidator = $formKeyValidator;
    }

    public function execute(){
        $result = $this->resultJson->create();

        $returnData = [];

        // 檢查 CSRF token
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            // CSRF token 驗證失敗，處理錯誤
            $returnData = [
                'success' => false,
                'code' => 201,
                'msg' => __('Invalid request parameters.')
            ];
        }

        $result->setData($returnData);

        return $result;
    }

}
