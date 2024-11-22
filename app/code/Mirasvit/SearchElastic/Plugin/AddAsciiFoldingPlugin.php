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


declare(strict_types=1);

namespace Mirasvit\SearchElastic\Plugin;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Mirasvit\Search\Model\ConfigProvider;

/**
 * @see \Magento\Elasticsearch\Model\Adapter\Index\Builder::build()
 */

class AddAsciiFoldingPlugin
{
    private $configProvider;

    public function __construct (
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }
    /**
     * add "asciifolding" for correct accented characters processing
     */
    public function afterBuild(Builder $subject, array $result): array
    {
        if ($this->configProvider->isAsciiFoldingAllowed()) {
            $result['analysis']['filter'][] = 'asciifolding';
            $result['analysis']['analyzer']['default']['filter'][] = 'asciifolding';
        }

        return $result;
    }
}
