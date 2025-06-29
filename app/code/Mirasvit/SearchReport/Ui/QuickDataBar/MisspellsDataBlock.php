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

namespace Mirasvit\SearchReport\Ui\QuickDataBar;

use Magento\Backend\Block\Template;
use Mirasvit\Core\Ui\QuickDataBar\SparklineDataBlock;
use Mirasvit\SearchReport\Api\Data\LogInterface;

class MisspellsDataBlock extends SparklineDataBlock
{
    private $dataProvider;

    public function __construct(
        DataProvider     $dataProvider,
        Template\Context $context
    ) {
        $this->dataProvider = $dataProvider;

        parent::__construct($context);
    }

    public function getLabel(): string
    {
        return (string)__('Misspells');
    }

    public function getScalarValue(): string
    {
        $value = $this->dataProvider->getScalarValue(
            new \Zend_Db_Expr('COUNT(log_id)'),
            new \Zend_Db_Expr('misspell_query IS NOT NULL AND misspell_query <> ""'),
            $this->dateFrom,
            $this->dateTo
        );

        return $this->dataProvider->number($value);
    }

    public function getSparklineValues(): array
    {
        $dateExpr = $this->getDateIntervalExpr(LogInterface::CREATED_AT);

        return $this->dataProvider->getSparklineValues(
            new \Zend_Db_Expr('COUNT(log_id)'),
            new \Zend_Db_Expr('misspell_query IS NOT NULL AND misspell_query <> ""'),
            $dateExpr,
            $this->dateFrom,
            $this->dateTo
        );
    }
}
