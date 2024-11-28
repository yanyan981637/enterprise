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

namespace Magezon\Blog\Block\Post;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Url;
use Magento\Framework\Registry;
use Magento\Framework\Url\EncoderInterface;
use Magezon\Blog\Block\Post\View;
use Magezon\Blog\Helper\Data;

class CommentForm extends View
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $postData = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param EncoderInterface $urlEncoder
     * @param Registry $registry
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        EncoderInterface $urlEncoder,
        Registry $registry,
        Data $dataHelper,
        array $data = []
    ) {
        $this->httpContext   = $httpContext;
        $this->urlEncoder    = $urlEncoder;
        $this->_coreRegistry = $registry;
        $this->dataHelper    = $dataHelper;
        parent::__construct($context, $registry, $dataHelper, $data);
    }

    /**
     * Initialize review form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setAllowPostCommentFlag(
            $this->httpContext->getValue(Context::CONTEXT_AUTH)
            || $this->dataHelper->getConfig('post_page/comments/allow_guests')
        );
        if (!$this->getAllowPostCommentFlag()) {
            $queryParam = $this->urlEncoder->encode(
                $this->getCurrentPost()->getUrl() . '#respond'
            );
            $this->setLoginLink(
                $this->getUrl(
                    'customer/account/login/',
                    [Url::REFERER_QUERY_PARAM_NAME => $queryParam]
                )
            );
        }
    }

    /**
     * Get value from POST by key
     *
     * @param string $key
     * @return string
     */
    public function getPostValue($key)
    {
        if (null === $this->postData) {
            $this->postData = (array) $this->getDataPersistor()->get('blog_comment_form');
            $this->getDataPersistor()->clear('blog_comment_form');
        }

        if (isset($this->postData[$key])) {
            return (string) $this->postData[$key];
        }

        return '';
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }
}
