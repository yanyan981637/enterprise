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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Ui\DataProvider\Import\Form;

use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magezon\Core\Ui\DataProvider\Form\AbstractModifier
{
    /**
     * @var \Magezon\Blog\Model\ResourceModel\Post\Collection
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
     * @param CollectionFactory           $postCollectionFactory 
     * @param DataPersistorInterface      $dataPersistor           
     * @param array                       $meta                    
     * @param array                       $data                    
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Registry $registry,
        CollectionFactory $postCollectionFactory,
        DataPersistorInterface $dataPersistor,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $postCollectionFactory->create();
        $this->registry      = $registry;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
        $this->pool = $pool;
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
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

        if ($this->dataPersistor->get('blog_import')) {
            $this->loadedData = $this->dataPersistor->get('blog_import');
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

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        $meta = $this->prepareMeta($meta);

        return $meta;
    }


    /**
     * @return mixed|null
     */
    public function getCurrentPost()
    {
        return $this->registry->registry('current_post');
    }
}
