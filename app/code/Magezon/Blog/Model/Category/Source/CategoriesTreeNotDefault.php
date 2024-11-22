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

namespace Magezon\Blog\Model\Category\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Registry;
use Magezon\Blog\Model\Category;
use Magezon\Blog\Model\ResourceModel\Category\CollectionFactory;

class CategoriesTreeNotDefault implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $_items;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Registry $registry,
        CollectionFactory $collectionFactory
    ) {
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $_option
     * @return array
     */
    protected function prepareOptions($_option)
    {
        $currentCategory = $this->getCurrentCategory();
        $childrens = [];
        foreach ($this->_items as $k => $_category) {
            if ($_category->getParentId() == $_option['value']
                && (!$currentCategory || ($currentCategory->getId() !== $_category->getId()))
            ) {
                $hasChildren = false;
                $children = [
                    'label' => $_category->getTitle(),
                    'value' => $_category->getId()
                ];
                foreach ($this->_items as $_category2) {
                    if ($_category2->getParentId() == $_category->getId()) {
                        $hasChildren = true;
                        break;
                    }
                }
                if ($hasChildren && ($_children = $this->prepareOptions($children))) {
                    $children['optgroup'] = $_children;
                }
                $childrens[] = $children;
            }
        }
        return $childrens;
    }

    /**
     * Get OptionSourceInterface
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $currentCategory = $this->getCurrentCategory();
        $collection = $this->collectionFactory->create();
        $collection->setOrder('category_id', 'ASC');
        $collection->setOrder('position', 'ASC');
        $items = $collection->getItems();

        foreach ($items as $k => $_category) {
            if (!$_category->getParentId() && (!$currentCategory || ($currentCategory->getId() !== $_category->getId()))) {
                $options[] = [
                    'label' => $_category->getTitle(),
                    'value' => $_category->getId()
                ];
                unset($items[$k]);
            }
        }
        $this->_items = $items;
        $this->_options = $options;


        foreach ($options as &$_option) {
            $children = $this->prepareOptions($_option);
            if ($children) $_option['optgroup'] = $children;
        }
        return $options;
    }

    /**
     * Retrive current category instance
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_blog_category');
    }
}
