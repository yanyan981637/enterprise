<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.1.8
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Block\Options;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Search\Model\ConfigProvider;

class GoogleSitelinks extends Template
{
    /**
     * @var ConfigProvider
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Context        $context
     * @param ConfigProvider $config
     * @param array          $data
     */
    public function __construct(
        Context $context,
        ConfigProvider $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Is enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isAddGoogleSiteLinks();
    }

    /**
     * Store base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->getUrl();
    }

    /**
     * Search target url
     *
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->getUrl(
            'catalogsearch/result/index',
            [
                '_query' => [
                    'q' => '{search_term_string}'
                ]
            ]
        );
    }
}
