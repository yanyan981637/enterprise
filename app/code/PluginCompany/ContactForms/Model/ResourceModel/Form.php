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

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Form extends AbstractDb
{
    const ADMIN_STORE_ID = 0;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTimeDateTime;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Filter\Translit
     */
    protected $frameworkTranslitHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plugincompany_contactforms_form', 'entity_id');
    }

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\Translit $frameworkTranslitHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeDateTime,
        ScopeConfigInterface $configScopeConfigInterface,
        $connectionName = null
    ){
        $this->dateTimeDateTime = $dateTimeDateTime;
        $this->storeManager = $storeManager;
        $this->frameworkTranslitHelper = $frameworkTranslitHelper;
        $this->scopeConfig = $configScopeConfigInterface;
        parent::__construct($context, $connectionName);
    }

    /**
     * Get store ids to which specified item is assigned
     * @access public
     * @param int $formId
     * @return array
     * @author Milan Simek
     */
    public function lookupStoreIds($formId){
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getTable('plugincompany_contactforms_form_store'), 'store_id')
            ->where('form_id = ?',(int)$formId);
        return $adapter->fetchCol($select);
    }



    /**
     * Perform operations after object load
     * @access public
     * @param AbstractModel $object
     * @return Form $object
     * @author Milan Simek
     */
    protected function _afterLoad(AbstractModel $object){
        if ($object->getId() && !is_array($object->getStoreId())) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        $this->conditionalAdminToFieldAfterLoad($object);
        return parent::_afterLoad($object);
    }

    /**
     * Assign form to store views
     * @access protected
     * @param AbstractModel $object
     * @return Form $object
     * @author Milan Simek
     */
    protected function _afterSave(AbstractModel $object){
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('plugincompany_contactforms_form_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = array(
                'form_id = ?' => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'form_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getLoadSelect($field, $value, $object){
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = array(self::ADMIN_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('contactforms_form_store' => $this->getTable('plugincompany_contactforms_form_store')),
                $this->getMainTable() . '.entity_id = contactforms_form_store.form_id',
                array()
            )
                ->where('contactforms_form_store.store_id IN (?)', $storeIds)
                ->order('contactforms_form_store.store_id DESC')
                ->limit(1);
        }
        return $select;
    }


    public function getFormIdForFrontEndPageUrlKey($urlKey, $storeId)
    {
        $stores = array(self::ADMIN_STORE_ID, $storeId);
        $select = $this->_initCheckUrlKeySelect($urlKey, $stores);

        $enabled = [1];
        if($this->isFrontEndPageEnabledByDefault()){
            $enabled[] = 2;
        }
        $select
            ->where('e.status = ?', true)
            ->where('e.frontend_page IN (?)', $enabled);
        ;
        $select
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns('e.entity_id')
            ->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    public function isFrontEndPageEnabledByDefault()
    {
        return (bool)$this->getStoreConfig('plugincompany_contactforms/form/frontendurl');
    }

    /**
     * check url key
     * @access public
     * @param string $urlKey
     * @param int $storeId
     * @param bool $active
     * @return mixed
     * @author Milan Simek
     */
    public function checkUrlKey($urlKey, $storeId, $active = true){
        $stores = array(self::ADMIN_STORE_ID, $storeId);
        $select = $this->_initCheckUrlKeySelect($urlKey, $stores);
        if ($active) {
            $select->where('e.status = ?', $active);
        }
        $select
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns('e.entity_id')
            ->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Check for unique URL key
     * @access public
     * @param AbstractModel $object
     * @return bool
     * @author Milan Simek
     */
    public function getIsUniqueUrlKey(AbstractModel $object){
        if ($this->storeManager->isSingleStoreMode() || !$object->hasStores()) {
            $stores = array(self::ADMIN_STORE_ID);
        }
        else {
            $stores = (array)$object->getData('stores');
        }
        $select = $this->_initCheckUrlKeySelect($object->getData('url_key'), $stores);
        if ($object->getId()) {
            $select->where('e.entity_id <> ?', $object->getId());
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }
    /**
     * Check if the URL key is numeric
     * @access public
     * @param AbstractModel $object
     * @return bool
     * @author Milan Simek
     */
    protected function isNumericUrlKey(AbstractModel $object){
        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
    }
    /**
     * Checkif the URL key is valid
     * @access public
     * @param AbstractModel $object
     * @return bool
     * @author Milan Simek
     */
    protected function isValidUrlKey(AbstractModel $object){
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('url_key'));
    }


    /**
     * format string as url key
     * @access public
     * @param string $str
     * @return string
     * @author Milan Simek
     */
    public function formatUrlKey($str) {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $this->frameworkTranslitHelper->filter($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey;
    }

    /**
     * init the check select
     * @access protected
     * @param string $urlKey
     * @param array $store
     * @return \Zend_Db_select
     * @author Milan Simek
     */
    protected function _initCheckUrlKeySelect($urlKey, $store){
        $select = $this->getConnection()->select()
            ->from(['e' => $this->getMainTable()])
            ->join(
                ['es' => $this->getTable('plugincompany_contactforms_form_store')],
                'e.entity_id = es.form_id',
                [])
            ->where('e.url_key = ?', $urlKey)
            ->where('es.store_id IN (?)', $store);
        return $select;
    }

    /**
     * validate before saving
     * @access protected
     * @param $object
     * @return Form
     * @author Milan Simek
     */
    protected function _beforeSave(AbstractModel $object){
        $now = $this->dateTimeDateTime->gmtDate();
        if ($object->isObjectNew()){
            $object->setCreatedAt($now);
        }
        $object->setUpdatedAt($now);
        $this->processUrlKey($object);
        $this->conditionalAdminToFieldBeforeSave($object);
        return parent::_beforeSave($object);
    }

    private function processUrlKey($object)
    {
        $urlKey = $object->getData('url_key');
        if ($urlKey == '') {
            $urlKey = $object->getTitle();
        }
        $urlKey = $this->formatUrlKey($urlKey);
        $validKey = false;
        while (!$validKey) {
            $entityId = $this->checkUrlKey($urlKey, $object->getStoreId(), false);
            if ($entityId == $object->getId() || empty($entityId)) {
                $validKey = true;
            }
            else {
                $parts = explode('-', $urlKey);
                $last = $parts[count($parts) - 1];
                if (!is_numeric($last)){
                    $urlKey = $urlKey.'-1';
                }
                else {
                    $suffix = '-'.($last + 1);
                    unset($parts[count($parts) - 1]);
                    $urlKey = implode('-', $parts).$suffix;
                }
            }
        }
        $object->setData('url_key', $urlKey);
        return $this;
    }

    private function conditionalAdminToFieldBeforeSave($object)
    {
        if (!is_array($object->getConditToEmail())) {
            return $this;
        }
        $conditEmailsToKeep = [];
        foreach($object->getConditToEmail() as $k => $item){
            if(isset($item['delete'])) continue;
            $conditEmailsToKeep[] = $item;
        }
        $object->setConditToEmail(json_encode($conditEmailsToKeep));
        return $this;
    }

    private function conditionalAdminToFieldAfterLoad($object)
    {
        if(!is_string($object->getConditToEmail()))
            return $this;

        $object->setConditToEmail(
            json_decode($object->getConditToEmail(), true)
        );
        return $this;
    }

    private function getStoreConfig($value)
    {
        return $this->scopeConfig->getValue($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
