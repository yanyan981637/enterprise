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

namespace Magezon\ProductAttachments\Model\File;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Convert\DataSize;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magezon\ProductAttachments\Model\File;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Magezon\ProductAttachments\Model\ResourceModel\File\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataSize
     */
    protected $convertSize;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DataPersistorInterface $dataPersistor
     * @param CollectionFactory $fileCollectionFactory
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        DataSize $convertSize,
        CollectionFactory $fileCollectionFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->convertSize = $convertSize;
        $this->collection = $fileCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        foreach ($items as $file) {
            $this->loadedData[$file->getId()] = $file->getData();
            if ($file->getType() == File::TYPE_FILE) {
                try {
                    $this->loadedData[$file->getId()]['file_upload'][0]['name'] = $file->getName();
                    $this->loadedData[$file->getId()]['file_upload'][0]['type'] = $file->getMimeType();
                    $this->loadedData[$file->getId()]['file_upload'][0]['size'] = $this->convertSize->convertSizeToBytes($file->getFileSize());
                    $this->loadedData[$file->getId()]['file_upload'][0]['url'] = $file->getFileUrl();
                } catch (\Exception $e) {
                }
            }
            $this->loadedData[$file->getId()]['file_data']['file_extension'] = $file->getFileExtention();
            $this->loadedData[$file->getId()]['file_data']['download_name'] = pathinfo($file->getDownloadName(), PATHINFO_FILENAME);
            if (!$file->getDownloadLimit()) {
                $this->loadedData[$file->getId()]['download_limit'] = "";
            }
        }

        $data = $this->dataPersistor->get('productattachments_file');
        if (!empty($data)) {
            $file = $this->collection->getNewEmptyItem();
            $file->setData($data);
            $this->loadedData[$file->getId()] = $file->getData();
            $this->dataPersistor->clear('productattachments_file');
        }

        return $this->loadedData;
    }
}
