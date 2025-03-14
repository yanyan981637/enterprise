<?php
/**
 *
 * 重寫 blog category view controller
 * 目的:
 *      1. 修改 layout
 *
 */
namespace Mitac\SecondaryDevelopmentExtensions\Controller\Blog\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Helper\Data as HelperBlog;

/**
 * Class View
 * @package Mageplaza\Blog\Controller\Category
 */
class View extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;


    /**
     * @var HelperBlog
     */
    public $helperBlog;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param HelperBlog $helperBlog
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        HelperBlog $helperBlog
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helperBlog = $helperBlog;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $category = $this->helperBlog->getFactoryByType(HelperBlog::TYPE_CATEGORY)->create()->load($id);

        if (!$this->helperBlog->checkStore($category)) {
            return $this->_redirect('noroute');
        }

        $page = $this->resultPageFactory->create();

        return $category->getEnabled() ? $page : $this->_redirect('noroute');
    }
}
