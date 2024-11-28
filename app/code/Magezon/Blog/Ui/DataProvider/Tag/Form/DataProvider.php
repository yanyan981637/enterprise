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

namespace Magezon\Blog\Ui\DataProvider\Tag\Form;

use Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magezon\Core\Ui\DataProvider\Form\AbstractModifier
{
    /**
     * @var \Magezon\Blog\Model\ResourceModel\Tag\Collection
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
     * @param CollectionFactory           $tagCollectionFactory 
     * @param DataPersistorInterface      $dataPersistor           
     * @param array                       $meta                    
     * @param array                       $data                    
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Registry $registry,
        CollectionFactory $tagCollectionFactory,
        DataPersistorInterface $dataPersistor,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $tagCollectionFactory->create();
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

        $tag = $this->getCurrentTag();
        if ($tag && $tag->getId()) {
            $this->loadedData[$tag->getId()] = $tag->getData();
        }

        $data = $this->dataPersistor->get('current_tag');
        if (!empty($data)) {
            $tag = $this->collection->getNewEmptyItem();
            $tag->setData($data);
            $this->loadedData[$tag->getId()] = $tag->getData();
            $this->dataPersistor->clear('current_tag');
        }

        if (is_array($this->loadedData)) {
            $imageAttributes = ['image', 'og_img'];
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
    public function getCurrentTag()
    {
        return $this->registry->registry('current_tag');
    }
}
