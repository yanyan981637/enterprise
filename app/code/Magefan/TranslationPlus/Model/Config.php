<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Last static content deploy datetime config path
     */
    const XML_PATH_LAST_STATIC_CONTENT_DEPLOY_DATETIME = 'mftranslation/general/date_time';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getStaticContentDeployDateTime(int $storeId = null): string
    {
        return (string)$this->getConfig(self::XML_PATH_LAST_STATIC_CONTENT_DEPLOY_DATETIME, $storeId);
    }

    /**
     * @param string $path
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfig(string $path, int $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
