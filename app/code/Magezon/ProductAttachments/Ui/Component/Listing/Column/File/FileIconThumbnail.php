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

namespace Magezon\ProductAttachments\Ui\Component\Listing\Column\File;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magezon\ProductAttachments\Helper\Data as DataHelper;
use Magezon\ProductAttachments\Model\FileFactory as FileFactory;
use Magezon\ProductAttachments\Model\ResourceModel\Icon\Collection as IconCollection;

class FileIconThumbnail extends Column
{
    const ALT_FIELD = 'title';

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var IconCollection
     */
    protected $iconCollection;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param DataHelper $dataHelper
     * @param FileFactory $fileFactory
     * @param IconCollection $iconCollection
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DataHelper $dataHelper,
        FileFactory $fileFactory,
        IconCollection $iconCollection,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->dataHelper = $dataHelper;
        $this->fileFactory = $fileFactory;
        $this->iconCollection = $iconCollection->addIsActiveFilter();
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $file = $this->fileFactory->create();
                $file->setData($item);
                $icon = $this->dataHelper->getIcon($this->iconCollection, $file);
                $item[$fieldName . '_src'] = $icon->getUrlIcon();
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_orig_src'] = $icon->getUrlIcon();
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
