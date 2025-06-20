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



namespace Mirasvit\Search\Index\Magento\Cms\Page;

use Mirasvit\Search\Index\AbstractBatchDataMapper;
use Mirasvit\Search\Index\Context;
use Mirasvit\Search\Service\ContentService;

class BatchDataMapper extends AbstractBatchDataMapper
{
    private $contentService;

    public function __construct(
        ContentService $contentService,
        Context $context
    ) {
        $this->contentService = $contentService;

        parent::__construct($context);
    }

    public function map(array $documentData, $storeId, array $context = [])
    {
        $documentData = parent::map($documentData, $storeId, $context);

        foreach ($documentData as $id => $doc) {
            foreach ($doc as $key => $value) {
                $documentData[$id][$key] = $this->contentService->processHtmlContent((int)$storeId, (string)$value);
            }
        }

        return $documentData;
    }
}
