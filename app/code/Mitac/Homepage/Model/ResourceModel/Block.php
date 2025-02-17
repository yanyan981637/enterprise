<?php
namespace Mitac\Homepage\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

use Mitac\Homepage\Api\Data\BlockInterface;

class Block extends AbstractDb
{
	protected function _construct()
	{
		$this->_init('mitac_homebanners', BlockInterface::KEY_ID);
	}
}
