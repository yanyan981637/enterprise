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

namespace Magezon\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magezon\Blog\Block\Adminhtml\Category\Tab\Post;

class Grid extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory    = $layoutFactory;
        $this->_coreRegistry    = $registry;
    }

    /**
     * @return Redirect|ResponseInterface|Raw|ResultInterface
     */
    public function execute()
    {
        $id       = $this->getRequest()->getParam('category_id');
        $category = $this->_objectManager->create('Magezon\Blog\Model\Category');
        $category->load($id);
        if (!$category) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('blog/*/', ['_current' => true, 'id' => null]);
        }
        $this->_coreRegistry->register('current_blog_category', $category);
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                Post::class,
                'category.post.grid'
            )->toHtml()
        );
    }
}
