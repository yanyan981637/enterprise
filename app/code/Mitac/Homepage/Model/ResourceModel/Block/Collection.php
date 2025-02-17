<?php
namespace Mitac\Homepage\Model\ResourceModel\Block;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

use Mitac\Homepage\Model\Block as Model;
use Mitac\Homepage\Model\ResourceModel\Block as ResourceModel;
use Mitac\Homepage\Api\Data\BlockInterface;

class Collection extends AbstractCollection
{
	protected $_idFieldName = BlockInterface::KEY_ID;

	protected function _construct()
	{
		$this->_init(Model::class, ResourceModel::class);
	}

}
