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

namespace Mirasvit\LayeredNavigation\Block\Renderer\Element;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;

class SearchBoxElement extends Template
{
    /** @var FilterInterface */
    private $filter;

    /** @var AttributeConfigInterface */
    private $attributeConfig;

    private $filterAccessor;

    public function setAttributeConfig(AttributeConfigInterface $attributeConfig): self
    {
        $this->attributeConfig = $attributeConfig;

        return $this;
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    public function setFilter(FilterInterface $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFilterAccessor(): string
    {
        return $this->filterAccessor;
    }

    public function setFilterAccessor(string $filterAccessor): self
    {
        $this->filterAccessor = $filterAccessor;

        return $this;
    }

    public function isShowSearchBox(): bool
    {
        return $this->attributeConfig->isShowSearchBox();
    }
}
