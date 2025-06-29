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



namespace Mirasvit\SearchMysql\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

class Wildcard implements FilterInterface
{
    const CONDITION_LIKE     = 'LIKE';
    const CONDITION_NOT_LIKE = 'NOT LIKE';

    private $conditionManager;

    public function __construct(
        ConditionManager $conditionManager
    ) {
        $this->conditionManager = $conditionManager;
    }

    public function buildFilter(RequestFilterInterface $filter, bool $isNegation): string
    {
        /** @var \Magento\Framework\Search\Request\Filter\Wildcard $filter */

        $searchValue = '%' . $filter->getValue() . '%';

        return $this->conditionManager->generateCondition(
            $filter->getField(),
            ($isNegation ? self::CONDITION_NOT_LIKE : self::CONDITION_LIKE),
            $searchValue
        );
    }
}
