<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Model\ResourceModel;


use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;

class BrandPage extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BrandPageInterface::TABLE_NAME, BrandPageInterface::ID);
    }

    /**
     * @return array
     */
    public function getAppliedOptionIds()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from(
                $this->_resources->getTableName(BrandPageInterface::TABLE_NAME),
                BrandPageInterface::ATTRIBUTE_OPTION_ID
            );

        return $connection->fetchCol($select);
    }

    /**
     * Call-back function.
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if (!$object->getIsMassStatus()) {
            $this->saveToStoreTable($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @return bool
     */
    protected function saveToStoreTable($object)
    {
        $condition = $this->getConnection()->quoteInto(BrandPageInterface::ID . ' = ?', $object->getId());
        $this->getConnection()->delete($this->getTable(BrandPageStoreInterface::TABLE_NAME), $condition);

        if ($object->getData('stores')) {
            foreach ((array)$object->getData('stores') as $storeData) {
                $this->insertStoreTableData($object, $storeData);
            }
        } else {
            $this->insertStoreTableData($object, ['store_id' => 0]);
        }
    }

    /**
     * @param AbstractModel $object
     * @param array         $storeData
     *
     * @return bool
     */
    protected function insertStoreTableData($object, $storeData)
    {
        $storeData[BrandPageInterface::ID] = $object->getId();

        $this->getConnection()->insert(
            $this->getTable(BrandPageStoreInterface::TABLE_NAME),
            $storeData
        );
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);

        $contentData = [];

        $select = $this->getConnection()->select()
            ->from($this->getTable(BrandPageStoreInterface::TABLE_NAME))
            ->where(BrandPageInterface::ID . ' = ' . $object->getId());

        foreach ($this->getConnection()->fetchAll($select) as $data) {
            $contentData[$data[BrandPageStoreInterface::STORE_ID]] = [
                BrandPageStoreInterface::BRAND_TITLE             => $data[BrandPageStoreInterface::BRAND_TITLE],
                BrandPageStoreInterface::BRAND_DESCRIPTION       => $data[BrandPageStoreInterface::BRAND_DESCRIPTION],
                BrandPageStoreInterface::BRAND_SHORT_DESCRIPTION => $data[BrandPageStoreInterface::BRAND_SHORT_DESCRIPTION],
            ];
        }

        $object->setData('content', $contentData);

        return $object;
    }
}
