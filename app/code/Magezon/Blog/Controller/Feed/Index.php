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

namespace Magezon\Blog\Controller\Feed;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Rss\Controller\Feed;
use Magento\Store\Model\ScopeInterface;

class Index extends Feed
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws NotFoundException
     * @throws InputException
     * @throws RuntimeException
     */
    public function execute()
    {
        $enabled = $this->scopeConfig->getValue('mgzblog/rss/enabled', ScopeInterface::SCOPE_STORE);
        if (!$enabled) {
            throw new NotFoundException(__('Page not found.'));
        }

        try {
            $provider = $this->rssManager->getProvider($this->getType());
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundException(__($e->getMessage()));
        }

        if (!$provider->isAllowed()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $rss = $this->rssFactory->create();
        $rss->setDataProvider($provider);

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->getResponse()->setBody($rss->createRssXml());
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getRequest()->getParam('type');
    }
}
