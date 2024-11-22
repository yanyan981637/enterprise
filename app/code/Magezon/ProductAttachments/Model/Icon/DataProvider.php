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

namespace Magezon\ProductAttachments\Model\Icon;

use Magento\Cms\Model\ResourceModel\Block\Collection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Magezon\ProductAttachments\Model\Icon;
use Magezon\ProductAttachments\Model\ResourceModel\Icon\CollectionFactory;
use Magezon\ProductAttachments\Model\IconUploader;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var Collection
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
     * @var
     */
    protected $mime;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $iconCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Filesystem $filesystem
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $iconCollectionFactory,
        DataPersistorInterface $dataPersistor,
        Filesystem $filesystem,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->collection = $iconCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
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
        /** @var Icon $block */
        foreach ($items as $model) {
            $mediaRootDir = $this->mediaDirectory->getAbsolutePath(IconUploader::BASE_PATH);
            $this->loadedData[$model->getId()] = $model->getData();
            if ($model->getFileName()) {
                try {
                    $size = $this->mediaDirectory->stat($mediaRootDir.$model->getFileName())['size'];
                    $this->loadedData[$model->getId()]['icon'][0]['name'] = $model->getFileName();
                    $this->loadedData[$model->getId()]['icon'][0]['type'] = 'image';
                    $this->loadedData[$model->getId()]['icon'][0]['size'] = $size;
                    $this->loadedData[$model->getId()]['icon'][0]['url'] = $model->getUrlIcon();
                } catch (\Exception $e) {
                }
                if ($model->getFileType()) {
                    foreach (explode(',', $model->getFileType()) as $key => $value) {
                        $this->loadedData[$model->getId()]['dynamic_rows'][$key] = [
                            'extension' => $value,
                            'record_id' => $key
                        ];
                    }
                }
            }
        }

        $data = $this->dataPersistor->get('productattachments_icon');
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$block->getId()] = $block->getData();
            $this->dataPersistor->clear('productattachments_icon');
        }

        return $this->loadedData;
    }
}
