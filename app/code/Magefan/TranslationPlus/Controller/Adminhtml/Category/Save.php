<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */


namespace Magefan\TranslationPlus\Controller\Adminhtml\Category;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action\Context;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magefan\Translation\Api\ConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /**
     * @var StoreRepositoryInterface
     */
    private $repository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param StoreRepositoryInterface $repository
     * @param CategoryRepository $categoryRepository
     * @param ConfigInterface $configInterface
     * @param ManagerInterface $messageManager
     * @param CategoryFactory $categoryCollectionFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $repository,
        CategoryRepository $categoryRepository,
        ConfigInterface $configInterface,
        ManagerInterface $messageManager,
        CollectionFactory $categoryCollectionFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
        $this->configInterface = $configInterface;
        $this->messageManager = $messageManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    /**
     * Saving category
     *
     * @return page
     */
    public function execute()
    {
        if (!$this->configInterface->isEnabled()) {
            return $this->processResponse(false, __(
                strrev(
                    'noitalsnarT> snoisnetxE nafegaM > noitarugifnoC >
            serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si noitalsnarT nafegaM'
                )
            ));
        }
        $post = $this->getRequest()->getPostValue();

        if (!isset($post['id'])) {
            return $this->processResponse(false, __('This category doesn\'t exist.'));
        }
        $id = (int)$post['id'];
        if (empty($id)) {
            return $this->processResponse(false, __('Invalid category id. Should be numeric value greater than 0'));
        }

        if (!isset($post[-1])) {
            return $this->processResponse(false, __('The all store view doesn\'t exist.'));
        }

        try {
            $category = $this->categoryRepository->get($id, null);
        } catch (NoSuchEntityException $e) {
            return $this->processResponse(false, $e->getMessage());
        }

        $post[0] = $post[-1];
        unset($post[-1]);

        try {
            $model = $this->categoryCollectionFactory->create();
            $resource     = $model->getResource();
            $adapter      = $resource->getConnection();

            foreach ($this->repository->getList() as $store) {
                if ($store->getId()) {
                    foreach ($post[0] as $attrKey => $value) {
                        if (!isset($post[$store->getId()][$attrKey])) {
                            $attribute = $resource->getAttribute($attrKey);
                            $condition = [
                                'attribute_id = ?'   => $attribute->getId(),
                                'store_id = ?'       => $store->getId(),
                                'entity_id IN(?)'    => $id
                            ];
                            $adapter->delete($attribute->getBackendTable(), $condition);
                        }
                    }
                }
            }

            ksort($post);
            foreach ($post as $storeId => $value) {
                if (!is_numeric($storeId)) {
                    continue;
                }

                $category = $this->categoryRepository->get($id, (int)$storeId);

                foreach ($post[$storeId] as $attrKey => $attrValue) {

                    if ($attrKey == 'category_has_weight') {
                        continue;
                    }
                    if (is_array($attrValue)) {
                        $attrValue = implode(',', $attrValue);
                    }

                    $category->setData($attrKey, $attrValue);
                    $category->getResource()->saveAttribute($category, $attrKey);
                }
            }
            return $this->processResponse(true, __('You saved the category.'));
        } catch (LocalizedException $e) {
            return $this->processResponse(false, $e->getPrevious() ? : $e);
        } catch (\Exception $err) {
            return $this->processResponse(false, __('Something went wrong while saving the category.'));
        }
    }

    /**
     * Process Response
     *
     * @param bool $success
     * @param text $message
     */
    private function processResponse($success, $message)
    {
        $id = $this->getRequest()->getParam('id');

        if ($this->getRequest()->isXmlHttpRequest()) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData([
                'success' => $success,
                'message' => (string)$message
            ]);
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();

            if ($success) {
                $this->messageManager->addSuccessMessage($message);

                $path = ($this->getRequest()->getParam('redirect'))
                    ? $this->getUrl('catalog/category/edit', ['id' => $id])
                    : $this->getUrl('translationplus/category/translate', ['id' => $id]);
            } else {
                $this->messageManager->addExceptionMessage($message);

                $path = ($id)
                    ? $this->getUrl('translationplus/category/translate', ['id' => $id])
                    : $this->getUrl('catalog/product/index');

            }

            return $resultRedirect->setPath($path);
        }
    }
}
