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

namespace Magezon\Blog\Ui\DataProvider\Comment\Form;

use Magezon\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\UrlInterface;

class DataProvider extends \Magezon\Core\Ui\DataProvider\Form\AbstractModifier
{
    /**
     * @var \Magezon\Blog\Model\ResourceModel\Comment\Collection
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
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param string                      $name                    
     * @param string                      $primaryFieldName        
     * @param string                      $requestFieldName        
     * @param \Magento\Framework\Registry $registry                
     * @param CollectionFactory           $commentCollectionFactory 
     * @param DataPersistorInterface      $dataPersistor           
     * @param array                       $meta                    
     * @param array                       $data                    
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Registry $registry,
        CollectionFactory $commentCollectionFactory,
        DataPersistorInterface $dataPersistor,
        UrlInterface $urlBuilder,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $commentCollectionFactory->create();
        $this->registry      = $registry;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
        $this->urlBuilder = $urlBuilder;
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
        $comment = $this->getCurrentComment();
        if ($comment && $comment->getId()) {
            $data = $comment->getData();
            $post = $comment->getPost();
            $data['post'] = '<a href="' . $this->urlBuilder->getUrl('blog/post/edit', ['post_id' => $post->getId()]) . '" target="_blank">' . $post->getTitle() . '</a>';
            if ($parent = $comment->getParent()) {
                $data['parent'] = '<a href="' . $this->urlBuilder->getUrl('blog/comment/edit', ['comment_id' => $parent->getId()]) . '" target="_blank">#' . $parent->getId() . '</a>';
                $data['enable_parent'] = true;
            } else {
                $data['enable_parent'] = false;
            }
            if ($customer = $comment->getCustomer()) {
                $data['customer'] = '<a href="' . $this->urlBuilder->getUrl('blog/comment/edit', ['comment_id' => $customer->getId()]) . '" target="_blank">' . $customer->getName() . '</a>';
                $data['enable_customer'] = true;
            } else {
                $data['enable_customer'] = false;
            }
            $this->loadedData[$comment->getId()] = $data;
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
    public function getCurrentComment()
    {
        return $this->registry->registry('current_comment');
    }
}
