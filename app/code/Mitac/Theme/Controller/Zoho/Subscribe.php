<?php

namespace Mitac\Theme\Controller\Zoho;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Mitac\Theme\Helper\Zoho\Request as ZohoRequest;

class Subscribe extends Action
{
    /**
     * @var FormKeyValidator
     * */
    protected $formKeyValidator;

    /**
     * @var JsonFactory
     */
    protected $resultJson;

    /**
     * @var ZohoRequest $zohoRequest
     */
    protected $zohoRequest;

    public function __construct(
        Context $context,
        JsonFactory $resultJson,
        FormKeyValidator $formKeyValidator,
        ZohoRequest $zohoRequest
    ){
        parent::__construct($context);
        $this->resultJson = $resultJson;
        $this->formKeyValidator = $formKeyValidator;
        $this->zohoRequest = $zohoRequest;
    }

    public function execute()
    {


        $result = $this->resultJson->create();

        $returnData = [];
        $email = $this->getRequest()->getParam('email');
        $type = $this->getRequest()->getParam('type');
         // 檢查 CSRF token
        if (
            !$this->formKeyValidator->validate($this->getRequest()) ||
            !$type ||
            !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            // CSRF token 驗證失敗，處理錯誤
            $returnData = [
                'success' => false,
                'code' => 201,
                'msg' => __('Invalid request parameters.')
            ];
            $result->setData($returnData);
            return $result;
        }

        try {

            $returnData = $this->zohoRequest->request($type, $email);

            $result->setData($returnData);
            return $result;
        }catch (\Exception $exception){
            $returnData = [
                'success' => false,
                'code' => 201,
                'msg' => $exception->getMessage()
            ];
            $result->setData($returnData);
            return $result;
        }

    }

}
