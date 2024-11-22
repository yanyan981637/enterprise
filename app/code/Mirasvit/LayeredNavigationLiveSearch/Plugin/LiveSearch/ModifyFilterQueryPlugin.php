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


namespace Mirasvit\LayeredNavigationLiveSearch\Plugin\LiveSearch;


use Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterQueryArgumentProcessor;
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;


class ModifyFilterQueryPlugin
{
    public function afterGetQueryArgumentValue(FilterQueryArgumentProcessor $subject, array $result): array
    {
        foreach ($result as $idx => $filterData) {
            if ($filterData['attribute'] !== ExtraFilterConfigProvider::RATING_FILTER) {
                continue;
            }

            $minValue  = min($filterData['in']);
            $newValues = range($minValue, 5);

            $result[$idx]['in'] = $newValues;
        }

        return $result;
    }
}
