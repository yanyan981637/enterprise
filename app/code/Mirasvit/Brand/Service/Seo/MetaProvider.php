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
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Service\Seo;

use Mirasvit\Brand\Model\Config\SeoConfig;
use Mirasvit\SeoNavigation\Model\Config\Source\MetaRobots;
use Mirasvit\SeoNavigation\Model\MetaInterface;

class MetaProvider implements MetaInterface
{
    const NAME = 'robots';

    /**
     * @var SeoConfig
     */
    private $config;

    /**
     * @var array
     */
    private $metaRobotsOptions;

    public function __construct(
        SeoConfig $config,
        MetaRobots $metaRobotsSource
    ) {
        $this->config            = $config;
        $this->metaRobotsOptions = $metaRobotsSource->toOptionArray();
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContent(): string
    {
        $meta = $this->config->getMeta();
        if (!$meta) {
            return $meta;
        }

        $key = array_search($meta, array_column($this->metaRobotsOptions, 'value'), true);

        return $this->metaRobotsOptions[$key]['label'];
    }
}
