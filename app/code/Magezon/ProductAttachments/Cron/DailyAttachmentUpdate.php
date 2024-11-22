<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Cron;

use Magezon\ProductAttachments\Model\Processor;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory;

class DailyAttachmentUpdate
{
    /**
     * @var CollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * DailyAttachmentUpdate constructor.
     *
     * @param CollectionFactory $fileCollectionFactory
     * @param Processor $processor
     */
    public function __construct(
        CollectionFactory $fileCollectionFactory,
        Processor $processor
    ) {
        $this->fileCollectionFactory = $fileCollectionFactory;
        $this->processor = $processor;
    }

    /**
     * Run process send product alerts
     *s
     * @return $this
     */
    public function process()
    {
        $collection = $this->fileCollectionFactory->create();
        foreach ($collection as $file) {
            $this->processor->process($file);
        }
    }
}
