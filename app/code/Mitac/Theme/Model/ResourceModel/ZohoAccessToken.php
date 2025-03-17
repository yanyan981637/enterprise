<?php
namespace Mitac\Theme\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ZohoAccessToken extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('zoho_access_token_manage', 'id');
    }

}
