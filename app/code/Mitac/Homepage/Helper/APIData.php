<?php
namespace Mitac\Homepage\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\AbstractHelper;

class APIData extends AbstractHelper
{
    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resource
    )
    {
        parent::__construct($context);
        $this->_resource = $resource;
    }

    public function getContentData($storeId, $type, $pageidentifier = '')
    {
        $returnArr = [];

        $connection = $this->_resource->getConnection();
        $tableBanners = $this->_resource->getTableName('mitac_homebanners');
        $tableStores = $this->_resource->getTableName('mitac_homebanner_stores');
        $tablePages = $this->_resource->getTableName('mitac_homebanner_pages');

        $select = $connection->select()
            ->from(['mh' => $tableBanners], ['title', 'text', 'button', 'img', 'url', 'youtube'])
            ->join(
                ['mhs' => $tableStores],
                'mh.banners_id = mhs.banners_id',
                []
            )
            ->joinLeft(
                ['mhp' => $tablePages],
                'mh.banners_id = mhp.banners_id',
                ['identifier']
            )
            ->where('mh.type = ?', $type)
            ->where('mh.status = ?', 1)
            ->where('mhs.stores_id IN (?)', [0, $storeId])
            ->order('sort_id ASC');

        if (!empty($pageidentifier)) 
        {
            $select->where('mhp.identifier = ?', $pageidentifier);
        }

        $result = $connection->fetchAll($select);

        foreach ($result as $rows)
        {
            $returnArr[] = $rows;
        }

        return $returnArr;
    }
}
