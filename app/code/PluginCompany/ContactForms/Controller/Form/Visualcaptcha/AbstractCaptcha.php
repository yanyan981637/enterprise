<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\ContactForms\Controller\Form\Visualcaptcha;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Store\Api\Data\StoreInterface;
use Psr\Log\LoggerInterface;
use \Exception;
use PluginCompany\ContactForms\Model\VisualCaptchaSession;

class AbstractCaptcha extends Action
{

    private $resultPageFactory;
    private $jsonHelper;
    private $logger;
    private $captcha;
    private $store;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $jsonHelper
     * @param LoggerInterface $logger
     * @param StoreInterface $store
     * @param VisualCaptchaSession $captcha
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $jsonHelper,
        LoggerInterface $logger,
        StoreInterface $store,
        VisualCaptchaSession $captcha
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->store = $store;
        $this->captcha = $captcha;
        parent::__construct($context);
    }

    /**
     * Execute view action
     */
    public function execute()
    {
        try {
            $this->runExecute();
        } catch (Exception $e) {
            $this->processError($e);
        }
    }

    public function runExecute()
    {
        return $this->sendJsonResponseMessage(
            'success',
            'testing'
        );
    }

    public function sendJsonResponseMessage($type, $message)
    {
        return $this->jsonResponse([
            'type' => $type,
            'message' => $message
        ]);
    }

    /**
     * Create json response
     *
     * @param string $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    public function processError($e)
    {
        $this->logger
            ->critical($e);
        $this->sendJsonResponseMessage(
            'error',
            'An error occured'
        );

        return $this;
    }

    public function getCaptcha()
    {
        return $this->captcha->getCaptcha(
            $this->getNamespace()
        );
    }

    public function getNamespace()
    {
        return $this->getRequest()->getParam('vcaptcha_namespace');
    }

    /**
     * @return PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * @return Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    protected function streamFile($file) {
        if ($file && is_array($file)) {
            /** @var \Magento\Framework\App\Response\Http $response */
            $response = $this->getResponse();
            foreach($file['headers'] as $header => $value) {
                $response->setHeader($header, $value);
            }
            $response
                ->setContent($file['content'])
            ;
        }else{
            $this->getResponse()->setContent(
                'pass?'
            );
        }
    }

}