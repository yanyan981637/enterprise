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



namespace Mirasvit\Search\Plugin\Frontend;

use Magento\Search\Model\Query as QueryModel;
use Magento\Search\Model\ResourceModel\Query;
use Mirasvit\Search\Service\BotDetectorService;

/**
 * @see \Magento\Search\Model\ResourceModel\Query::saveNumResults()
 * @see \Magento\Search\Model\ResourceModel\Query::saveIncrementalPopularity()
 */
class BotDetectorPlugin extends Query
{
    private $botDetectorService;

    public function __construct (
        BotDetectorService $botDetectorService
    ){
        $this->botDetectorService = $botDetectorService;
    }
    public function aroundSaveNumResults(Query $subject, callable $proceed, QueryModel $query): void
    {
        if (!$this->botDetectorService->isBotQuery($query->getQueryText())) {
            $proceed($query);
        }
    }

    public function aroundSaveIncrementalPopularity(Query $subject, callable $proceed, QueryModel $query): void
    {
        if (!$this->botDetectorService->isBotQuery($query->getQueryText())) {
            $proceed($query);
        }
    }
}
