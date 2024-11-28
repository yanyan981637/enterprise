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
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductLabels\Ui\DataProvider\Form;

use Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magezon\Core\Ui\DataProvider\Form\AbstractModifier
{
    /**
     * @var \Magezon\ProductLabels\Model\ResourceModel\Label\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @param string                      $name
     * @param string                      $primaryFieldName
     * @param string                      $requestFieldName
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory           $questionCollectionFactory
     * @param DataPersistorInterface      $dataPersistor
     * @param array                       $meta
     * @param array                       $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Registry $registry,
        CollectionFactory $labelCollectionFactory,
        DataPersistorInterface $dataPersistor,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $labelCollectionFactory->create();
        $this->registry      = $registry;
        $this->dataPersistor = $dataPersistor;
        $this->pool          = $pool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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

        $label = $this->getCurrentLabel();
        if ($label && $label->getId()) {
            $this->loadedData[$label->getId()] = $label->getData();

        }

        $data = $this->dataPersistor->get('productlabels_label');

            
        if (!empty($label)) {
            $label = $this->collection->getNewEmptyItem();


            $label->setData($data);
            $this->loadedData[$label->getId()] = $label->getData();
            $this->dataPersistor->clear('productlabels_label');
        }
        if (is_array($this->loadedData)) {
            $imageAttributes = ['productpage_image', 'productlist_image'];
            $original = reset($this->loadedData);
            foreach ($imageAttributes as $_attr) {
                $attributeTmp = $_attr . '_tmp';
                if (isset($original[$_attr]) && $original[$_attr] && $this->getFileInfo()->isExist($original[$_attr])) {
                    $data         = [];
                    $fileName     = $original[$_attr];
                    $stat         = $this->getFileInfo()->getStat($fileName);
                    $mime         = $this->getFileInfo()->getMimeType($fileName);
                    $data['name'] = $fileName;
                    $data['url']  = $this->getFileInfo()->getFileUrl($fileName);
                    $data['size'] = isset($stat) ? $stat['size'] : 0;
                    $data['type'] = $mime;
                    $original[$attributeTmp] = $data;
                } else {
                    unset($original[$attributeTmp]);
                }
            }
            $key = key($this->loadedData);
            $this->loadedData[$key] = $original;
        }

        if (is_array($this->loadedData)) {
            $data = reset($this->loadedData);
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $data = $modifier->modifyData($data);
            }
            $key = key($this->loadedData);
            $this->loadedData[$key] = $data;
        }
        return $this->loadedData;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }


    /**
     * Get current label
     *
     * @return label
     * @throws NoSuchEntityException
     */
    public function getCurrentLabel()
    {
        return $this->registry->registry('productlabels_label');
    }
}
