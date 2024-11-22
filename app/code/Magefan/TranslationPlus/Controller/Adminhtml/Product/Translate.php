<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Controller\Adminhtml\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductRepository;
use Magefan\Translation\Api\ConfigInterface;

class Translate extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param ProductRepository $productRepository
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        ProductRepository $productRepository,
        ConfigInterface $configInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->configInterface = $configInterface;

        return parent::__construct($context);
    }

    /**
     * Generate and return page
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        if (!$this->configInterface->isEnabled()) {
            $this->messageManager->addError(
                __(
                    strrev(
                        'noitalsnarT> snoisnetxE nafegaM > noitarugifnoC >
            serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si noitalsnarT nafegaM'
                    )
                )
            );

            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('admin/index/index');
        }

        try {
            $product = $this->productRepository->getById((int) $this->getRequest()->getParam('id'), false, 0, false);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath($this->getUrl('catalog/product/index'));
        }

        $this->registry->register('product', $product);



        return $resultPage;
    }
}
