<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block;

use Magefan\Translation\Model\ResourceModel\Translation\CollectionFactory;
use Magefan\Translation\Model\Config;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObject\IdentityInterface;
use Magefan\Community\Api\SecureHtmlRendererInterface;

class TranslationJson extends \Magento\Framework\View\Element\Text implements IdentityInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var SecureHtmlRendererInterface
     */
    private $mfSecureRenderer;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param Resolver $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param Json $jsonSerializer
     * @param array $data
     * @param SecureHtmlRendererInterface|null $mfSecureRenderer
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $config,
        Resolver $localeResolver,
        StoreManagerInterface $storeManager,
        Json $jsonSerializer,
        array $data = [],
        SecureHtmlRendererInterface $mfSecureRenderer = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->mfSecureRenderer = $mfSecureRenderer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(SecureHtmlRendererInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getLastUpdatedAt()
    {
        $locale = $this->localeResolver->getLocale();
        $storeId = $this->storeManager->getStore()->getId();

        $translations = $this->collectionFactory->create();
        $translations
            ->addFieldToFilter('locale', $locale)
            ->addFieldToFilter(
                ['store_id', 'store_id'],
                [
                    ['eq' => 0],
                    ['eq' => $storeId],
                ]
            )
            ->setOrder('updated_at', 'DESC')
            ->setPageSize(1);

        $lastTranslation = $translations->getFirstitem();
        return $lastTranslation->getUpdatedAt();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTranslationJson(): string
    {
        $isDisableTranslationParams = (bool)$this->config->getConfig('mftranslation/general/disable_translation_config_params');
        if ($isDisableTranslationParams) {
            $data = [];
        } else {
            $data = [
                'locale' => $this->localeResolver->getLocale(),
                'store_id' => $this->storeManager->getStore()->getId(),
                'timestamp' => strtotime($this->getLastUpdatedAt() ?: '')
            ];
        }

        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Override this method in descendants to produce html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->isEnabled()) {
            return '';
        }

        $json = $this->getTranslationJson();
        if ($json) {
            $script = 'window.mfTranslationConfig=' . $json . ';' ;
            return $this->mfSecureRenderer->renderTag('script', [], $script, false);
        }

        return '';
    }

    /**
     * Retrieve identities
     *
     * @return array
     */
    public function getIdentities()
    {
        if ($this->config->isEnabled()) {
            $identities = [];
            $identities[] = \Magefan\Translation\Model\Translation::CACHE_TAG . '_' . 0;
            return $identities;
        }

        return [];
    }
}
