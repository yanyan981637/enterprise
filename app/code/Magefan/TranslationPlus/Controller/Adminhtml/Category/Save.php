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
use Magento\Framework\Event\ManagerInterface as EventManager;

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

    private $eventManager;

    /**
     * @var array
     */
    private $use301redirect = [];

    /**
     * @param Context $context
     * @param StoreRepositoryInterface $repository
     * @param CategoryRepository $categoryRepository
     * @param ConfigInterface $configInterface
     * @param ManagerInterface $messageManager
     * @param CollectionFactory $categoryCollectionFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $repository,
        CategoryRepository $categoryRepository,
        ConfigInterface $configInterface,
        ManagerInterface $messageManager,
        CollectionFactory $categoryCollectionFactory,
        JsonFactory $resultJsonFactory,
        EventManager $eventManager
    ) {
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
        $this->configInterface = $configInterface;
        $this->messageManager = $messageManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventManager = $eventManager;
        parent::__construct($context);
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

        $this->collect301Redirects($post);

        try {
            $model = $this->categoryCollectionFactory->create();
            $resource     = $model->getResource();
            $adapter      = $resource->getConnection();

            foreach ($this->repository->getList() as $store) {
                if ($store->getId()) {
                    foreach ($post[0] as $attrKey => $value) {
                        if (!isset($post[$store->getId()][$attrKey])) {
                            if ($attrKey == 'url_key') {
                                // in case when checkbox Use Default Checked
                                $post[$store->getId()]['url_key'] = false;
                                $post[$store->getId()]['url_key_create_redirect'] = $this->use301redirect[$store->getId()] ?? 0;
                                continue;
                            }

                            $attribute = $resource->getAttribute($attrKey);

                            if (!$attribute) {
                                continue;
                            }

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
                    if (is_array($attrValue)) {
                        $attrValue = implode(',', $attrValue);
                    }

                    switch ($attrKey) {
                        case "url_key_create_redirect":
                        case "category_has_weight":
                        case "locale_code":
                        case "auto_translation_original":
                            break;
                        case "url_key":
                            $this->saveUrlKey($category, $post[$storeId], $storeId, $model);
                            break;
                        default:
                            $category->setData($attrKey, $attrValue);
                            $category->getResource()->saveAttribute($category, $attrKey);
                    }
                }
            }
            $this->eventManager->dispatch('magefan_save_catalog_translation', ['current_object' => $this]);
            return $this->processResponse(true, __('You saved the category.'));
        } catch (LocalizedException $e) {
            return $this->processResponse(false, $e->getPrevious() ? : $e);
        } catch (\Exception $err) {
            return $this->processResponse(false, __('Something went wrong while saving the category.'));
        }
    }

    /**
     * @param $category
     * @param $data
     * @param $storeId
     * @param $model
     * @return void
     * @throws NoSuchEntityException
     */
    private function saveUrlKey($category, $data, $storeId, $model)
    {
        $oldUrl = $category->getUrlKey();
        $newUrl = $data['url_key'];

        if (($newUrl || $newUrl === '') && $oldUrl != $newUrl) {

            // in case of empty string - url key will be generated based on entity name
            if ($newUrl === '') {
                $newUrl = null;
            }

            $category->setData('url_key_create_redirect', '');
            $category->setData('save_rewrites_history', false);

            if (isset($data['url_key_create_redirect']) && $data['url_key_create_redirect']) {
                $category->setData('url_key_create_redirect', $oldUrl);
                $category->setData('save_rewrites_history', true);
            }

            $category->setUrlKey($newUrl);
            $this->_eventManager->dispatch('catalog_category_save_before', ['category' => $category]);
            $category->getResource()->saveAttribute($category, 'url_key');

            $this->_eventManager->dispatch('catalog_category_save_after', ['category' => $category]);
        } elseif ($newUrl === false && $category->getExistsStoreValueFlag('url_key')) {
            // in case when use_default checked

            $category->setData('url_key_create_redirect', '');
            $category->setData('save_rewrites_history', true);

            $categoryBaseStore = $this->categoryRepository->get($category->getData('entity_id'), 0);

            $category->setUrlKey($categoryBaseStore->getUrlKey());

            $this->_eventManager->dispatch('catalog_category_save_before', ['category' => $category]);
            $category->getResource()->saveAttribute($category, 'url_key');
            $this->_eventManager->dispatch('catalog_category_save_after', ['category' => $category]);

            // remove value from store level
            $resource = $model->getResource();
            $adapter = $resource->getConnection();
            $attribute = $resource->getAttribute('url_key');

            $condition = [
                'attribute_id = ?'   => $attribute->getId(),
                'store_id = ?'       => $storeId,
                'entity_id IN(?)'    => $category->getData('entity_id')
            ];

            $adapter->delete($attribute->getBackendTable(), $condition);
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
                $this->messageManager->addErrorMessage($message);

                $path = ($id)
                    ? $this->getUrl('translationplus/category/translate', ['id' => $id])
                    : $this->getUrl('catalog/product/index');

            }

            return $resultRedirect->setPath($path);
        }
    }

    /**
     * @param array $post
     */
    private function collect301Redirects(array &$post): void
    {
        $useRedirectForDefaultValue = $post['use_redirect_for_default_value'] ?? [];

        if ($useRedirectForDefaultValue) {

            foreach ($useRedirectForDefaultValue as $storeId_isChecked) {
                [$storeId, $isChecked] = explode('_', $storeId_isChecked);

                $this->use301redirect[$storeId] = $isChecked;
            }

            unset($post['use_redirect_for_default_value']);
        }
    }
}
