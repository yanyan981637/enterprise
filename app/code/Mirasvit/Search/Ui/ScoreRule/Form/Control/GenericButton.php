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

namespace Mirasvit\Search\Ui\ScoreRule\Form\Control;

use Magento\Backend\Block\Widget\Context;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;

class GenericButton
{
    private $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function getId(): ?int
    {
        return (int) $this->context->getRequest()->getParam(ScoreRuleInterface::ID);
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
