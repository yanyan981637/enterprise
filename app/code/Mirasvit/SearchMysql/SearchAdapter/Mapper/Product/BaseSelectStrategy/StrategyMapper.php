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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\BaseSelectStrategy;

use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\SelectContainer\SelectContainer;

class StrategyMapper
{
    private $baseSelectFullTextSearchStrategy;

    private $baseSelectAttributesSearchStrategy;

    public function __construct(
        BaseSelectFullTextSearchStrategy $baseSelectFullTextSearchStrategy,
        BaseSelectAttributesSearchStrategy $baseSelectAttributesSearchStrategy
    ) {
        $this->baseSelectFullTextSearchStrategy   = $baseSelectFullTextSearchStrategy;
        $this->baseSelectAttributesSearchStrategy = $baseSelectAttributesSearchStrategy;
    }


    /**
     * @return BaseSelectAttributesSearchStrategy|BaseSelectFullTextSearchStrategy
     */
    public function mapSelectContainerToStrategy(SelectContainer $selectContainer)
    {
        if ($selectContainer->isFullTextSearchRequired()
            && !$selectContainer->hasCustomAttributesFilters()
            && !$selectContainer->hasVisibilityFilter()
        ) {
            return $this->baseSelectFullTextSearchStrategy;
        }

        return $this->baseSelectAttributesSearchStrategy;
    }
}
