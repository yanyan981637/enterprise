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

namespace Mirasvit\LayeredNavigation\Plugin\Frontend;

use Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;

/**
 * Reset price algorithm instance cache (in other case, facet for price is wrong)
 * Required for 2.4
 * @see \Magento\Framework\Search\Dynamic\Algorithm\Repository::get()
 */
class ResetPriceAlgorithmCachePlugin
{
    public function afterGet(object $subject, AlgorithmInterface $result): AlgorithmInterface
    {
        $obj = new \ReflectionClass(Repository::class);
        //2.4.4 does not have this property
        if ($obj->hasProperty('instances')) {
            $ref = new \ReflectionProperty(Repository::class, 'instances');
            $ref->setAccessible(true);
            $ref->setValue($subject, []);
        }

        return $result;
    }
}
