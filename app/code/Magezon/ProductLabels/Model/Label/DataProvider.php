<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @author    Hoang PB - hoangpb@magezon.com
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductLabels\Model\Label;
 
use Magento\Framework\App\Request\DataPersistorInterface;
use Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var \Magezon\ProductLabels\Model\ResourceModel\Label\CollectionFactory
     */
    protected $collection;

    /**
     * @param string                 $name                     
     * @param string                 $primaryFieldName         
     * @param string                 $requestFieldName         
     * @param DataPersistorInterface $dataPersistor            
     * @param CollectionFactory      $labelCollectionFactory 
     * @param array                  $meta                     
     * @param array                  $data                     
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $labelCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->dataPersistor = $dataPersistor;
        $this->collection    = $labelCollectionFactory->create();
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
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }
        $data = $this->dataPersistor->get('productlabels_label'); 
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$block->getId()] = $block->getData();
            $this->dataPersistor->clear('productlabels_label');
        }
        return $this->loadedData;
    }
}