<?php

namespace Mitac\SecondaryDevelopmentExtensions\Controller\Blog\Post;
/**
 *
 * 重寫 blog Root category view controller
 * 目的:
 *      1. 修改 layout
 *
 */
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Helper\Data;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var Data
     */
    protected $_helperBlog;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helperData
    ) {
        $this->_helperBlog = $helperData;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $page = $this->resultPageFactory->create();

        return $page;
    }
}
