<?php
namespace Mitac\Homepage\Model\ResourceModel\ImageUploader;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

use Mitac\Homepage\Model\ImageUploader as Model;
use Mitac\Homepage\Model\ResourceModel\ImageUploader as ResourceModel;

class Collection extends AbstractCollection
{
	protected function _construct()
	{
		$this->_init(Model::class, ResourceModel::class);
	}

}
