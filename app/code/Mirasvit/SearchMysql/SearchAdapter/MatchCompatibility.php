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



namespace Mirasvit\SearchMysql\SearchAdapter;

use Mirasvit\Core\Service\CompatibilityService;
/** mp comment start **/
if (version_compare(CompatibilityService::getVersion(), '2.4.4', '<')) {
    class MatchCompatibility extends \Magento\Framework\Search\Request\Query\Match
    {
        protected $matches = [];

        public function __construct(?string $name, $value, $boost, array $matches)
        {
            parent::__construct($name, $value, $boost, $matches);
        }
    }
} else {
/** mp comment end **/
    class MatchCompatibility extends \Magento\Framework\Search\Request\Query\MatchQuery
    {
        protected $matches = [];

        public function __construct(?string $name, $value, $boost, array $matches)
        {
            parent::__construct($name, $value, $boost, $matches);
        }
    }
/** mp comment start **/
}
/** mp comment end **/
