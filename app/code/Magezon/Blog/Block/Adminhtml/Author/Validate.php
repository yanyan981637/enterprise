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
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Block\Adminhtml\Author;

use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Template;

class Validate extends Template
{
    /**
     * template
     *
     * @var string
     */
    protected $_template = 'Magezon_Blog::validate/author.phtml';

    /**
     * @var array
     */
    private $authorUrlKeys;

    /**
     * @return array
     */
    public function getAllAuthorUrlKey() {
        if (!$this->authorUrlKeys) {
            $authorCollection = ObjectManager::getInstance()->get(
                \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory::class
            )->create();
            $authorId = $this->getRequest()->getParam('author_id');
            if ($authorId) {
                $authorCollection->addFieldToFilter('author_id', array('neq' => $authorId));
            }
            $authorUrlKeys = $authorCollection->addFieldToSelect('identifier')->getData();
            $urlKeys = [];
            foreach ($authorUrlKeys as $value) {
                $urlKeys[] = $value['identifier'];
            }
            $this->authorUrlKeys = $urlKeys;
        }
        return $this->authorUrlKeys;
    }
}