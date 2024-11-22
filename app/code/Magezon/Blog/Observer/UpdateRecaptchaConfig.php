<?php

namespace Magezon\Blog\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class UpdateRecaptchaConfig implements ObserverInterface
{
    /**
     * Magento Blog section configuration name
     */
    const MGZBLOG_SECTION = "mgzblog";

    /**
     * Magento Blog comment recaptcha type
     */
    const MGZBLOG_COMMENT_RECAPTCHA_TYPE_PATH = "mgzblog/post_page/comments/recaptcha/recaptcha_type";

    /**
     * Recaptcha config in Security
     */
    const RECAPTCHA_FRONTEND_BLOG_COMMENT = "recaptcha_frontend/type_for/blog_comment_form";


    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     */
    public function __construct(
        RequestInterface $request,
        WriterInterface  $configWriter
    )
    {
        $this->request = $request;
        $this->configWriter = $configWriter;

    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $params = $this->request->getParams();
        if ($params['section'] == self::MGZBLOG_SECTION) {
            $recaptchaType = $params["groups"]["post_page"]["groups"]
            ["comments"]["groups"]["recaptcha"]["fields"]["recaptcha_type"];
            if(array_key_exists('value', $recaptchaType)){
                $this->configWriter->save(self::RECAPTCHA_FRONTEND_BLOG_COMMENT, $recaptchaType['value'],
                    $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0
                );
            }
        } else {
            $recaptchaType = $params["groups"]["type_for"]["fields"]["blog_comment_form"];
            if(array_key_exists('value', $recaptchaType)){
                $this->configWriter->save(self::MGZBLOG_COMMENT_RECAPTCHA_TYPE_PATH,
                    $recaptchaType['value'],
                    $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0
                );
            }
        }
    }
}
