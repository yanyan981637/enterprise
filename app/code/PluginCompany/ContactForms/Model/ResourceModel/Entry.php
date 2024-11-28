<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 * 
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 * 
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 * 
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 * 
 * SUPPORT@PLUGIN.COMPANY
 */

namespace PluginCompany\ContactForms\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Model\AbstractModel;

class Entry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTimeDateTime;

    public function __construct(
        Context $context,
        DateTime $dateTimeDateTime,
        $connectionName = null
    )
    {
        $this->dateTimeDateTime = $dateTimeDateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plugincompany_contactforms_entry', 'entity_id');
    }

    protected function _beforeSave(AbstractModel $object)
    {
        $now = $this->dateTimeDateTime->gmtDate();
        if ($object->isObjectNew()){
            $object->setCreatedAt($now);

        }
        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {
        if($object->isObjectNew()){
            $object->getForm()
                ->increaseEntryIncrementIdCounter()
                ->save()
            ;
        }
        return $this;
    }

}
