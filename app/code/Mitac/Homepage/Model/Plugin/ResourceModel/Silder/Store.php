<?php
namespace Mitac\Homepage\Model\Plugin\ResourceModel\Silder; 
 
class Store
{
    public static $table = 'mitac_homebanners';
    public static $leftJoinTable = 'store';         // My custom table
 
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) 
        {
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
 
            $collection
                ->getSelect()
                ->joinLeft(
                    ['cb'=>$leftJoinTableName],
                    "cb.store_id  = main_table.stores_id"
                );
 
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
 
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where)->group('main_table.banners_id');;
        }

        return $collection;
    }

}