<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model;

use Magefan\TranslationPlus\Api\Data\TranslationIndexInterface;
use Magefan\TranslationPlus\Api\Data\TranslationIndexInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Setup\Exception;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Config\Model\ResourceModel\Config;
use Magefan\TranslationPlus\Model\JsTranslation;
use Magefan\TranslationPlus\Model\PhrasesTranslations;
use Magefan\TranslationPlus\Model\Config\Source\UsedInArea;

/**
 * @method addComponent(mixed $ATTRIBUTE_CODE, $column)
 */
class TranslationIndex extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var TranslationIndexInterfaceFactory
     */
    protected $translationIndexDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var string
     */
    protected $_eventPrefix = 'mftranslation_index';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var \Magefan\TranslationPlus\Model\JsTranslation|mixed
     */
    private $jsTranslation;

    /**
     * @var PhrasesTranslations|mixed
     */
    private $phrasesTranslations;

    /**
     * @var array
     */
    private $phrasesTranslationsData = [];

    /**
     * @param Context $context
     * @param Registry $registry
     * @param TranslationIndexInterfaceFactory $translationIndexDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param SchemaSetupInterface $schemaSetup
     * @param ScopeConfigInterface $scopeConfig
     * @param Emulation $emulation
     * @param Manager $cacheManager
     * @param DateTime $date
     * @param Config $resourceConfig
     * @param array $data
     * @param \Magefan\TranslationPlus\Model\JsTranslation|null $jsTranslation
     * @param \Magefan\TranslationPlus\Model\PhrasesTranslations|null $phrasesTranslations
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TranslationIndexInterfaceFactory $translationIndexDataFactory,
        DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        SchemaSetupInterface $schemaSetup,
        ScopeConfigInterface $scopeConfig,
        Emulation $emulation,
        Manager $cacheManager,
        DateTime $date,
        Config $resourceConfig,
        array $data = [],
        JsTranslation $jsTranslation = null,
        PhrasesTranslations $phrasesTranslations = null
    ) {
        $this->translationIndexDataFactory = $translationIndexDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->schemaSetup = $schemaSetup;
        $this->scopeConfig = $scopeConfig;
        $this->emulation = $emulation;
        $this->cacheManager = $cacheManager;
        $this->date = $date;
        $this->resourceConfig = $resourceConfig;
        $this->jsTranslation = $jsTranslation ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(JsTranslation::class);
        $this->phrasesTranslations = $phrasesTranslations ?: \Magento\Framework\App\ObjectManager::getInstance()->get(PhrasesTranslations::class);
        parent::__construct($context, $registry);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\TranslationPlus\Model\ResourceModel\TranslationIndex::class);
    }

    /**
     * @return TranslationIndexInterface
     */
    public function getDataModel()
    {
        $TranslationIndexData = $this->getData();

        $TranslationIndexDataObject = $this->translationIndexDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $TranslationIndexDataObject,
            $TranslationIndexData,
            TranslationIndexInterface::class
        );

        return $TranslationIndexDataObject;
    }

    /**
     * @return bool
     * @throws \Zend_Db_Exception
     */
    public function updateSchema()
    {
        if (!$this->isSchemaExist()) {
            $this->installSchema();
        } elseif ($this->isSchemaChanaged()) {
            $this->removeSchema();
            $this->installSchema();
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Zend_Db_Exception
     */
    private function isSchemaChanaged()
    {
        $locales = $this->getAllStoreLocale();
        $installer = $this->schemaSetup;
        $connection = $installer->getConnection();
        $tableWithPrefix = $connection->getTableName($this->getResource()->getMainTable());
        $tableStructure = $connection->describeTable($installer->getTable($tableWithPrefix));

        $tableColumns = [];
        foreach ($tableStructure as $columnName) {
            $tableColumns[] = $columnName['COLUMN_NAME'];
        }
        $sameLocales = true;
        foreach ($locales as $locale) {
            if (!in_array(strtolower($locale), $tableColumns)) {
                $sameLocales = false;
                break;
            }
        }

        return !$sameLocales;
    }

    /**
     * @return bool
     */
    public function isSchemaExist()
    {
        return $this->schemaSetup->tableExists(
            $this->getResource()->getMainTable()
        );
    }

    /**
     * Remove schema
     */
    private function removeSchema()
    {
        $installer = $this->schemaSetup;
        $connection = $this->resource->getConnection();
        $tableWithPrefix = $connection->getTableName(
            $installer->getTable($this->getResource()->getMainTable())
        );
        $connection->dropTable($installer->getTable($tableWithPrefix));
    }

    /**
     * @throws \Zend_Db_Exception
     */
    public function installSchema()
    {
        $locales = $this->getAllStoreLocale();
        $installer = $this->schemaSetup;

        try {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mftranslation_index')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Index Translation Id'
            )->addColumn(
                'string',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64K',
                ['nullable' => false],
                'String'
            )->addColumn(
                'crc_string',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => false],
                'Crc string'
            )->addColumn(
                'source',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64K',
                ['nullable' => false],
                'Source'
            )->addColumn(
                'used_in_area',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                5,
                ['nullable' => false],
                'Used In Area'
            )->addIndex(
                $installer->getIdxName($installer->getTable('mftranslation_index'), ['used_in_area']),
                ['used_in_area']
            )->addColumn(
                'module',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64K',
                ['nullable' => false],
                'Module'
            )->addColumn(
                'path_to_string',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64K',
                ['nullable' => false],
                'Path to String'
                );

            if (isset($locales)) {
                foreach ($locales as $locale) {
                    $table->addColumn(
                        strtolower($locale),
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        '64K',
                        ['nullable' => false],
                        'Locale code'
                    );
                    $table->addColumn(
                        strtolower($locale) . '_translated',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        '64K',
                        ['nullable' => false],
                        'If translated'
                    );
                }
            }
            $installer->getConnection()->createTable($table);
        } catch (Exception $err) {
            $this->_logger->info($err->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getAllStoreLocale()
    {
        $result = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->getIsActive()) {
                continue;
            }
            $localeCode = $this->scopeConfig->getValue(
                'general/locale/code',
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
            $result[] = $localeCode;
        }
        return array_unique($result);
    }

    /**
     * @return array|false
     */
    private function getTablesContent()
    {
        $source = [];
        $strings = [];
        $selectedTables = [
            'cms_block' => 'content',
            'cms_page' => 'content',
            'catalog_product_entity_varchar' => 'value',
            'catalog_product_entity_text' => 'value',
            'catalog_category_entity_varchar' => 'value',
            'catalog_category_entity_text' => 'value',
            'newsletter_template' => 'template_text',
            'same_newsletter_template' => 'template_subject',
            'email_template' => 'template_text',
            'same_email_template' => 'template_subject'
        ];

        $nameIds = [
            'cms_block' => 'block_id',
            'cms_page' => 'page_id',
            'catalog_product_entity_varchar' => 'value_id',
            'catalog_product_entity_text' => 'value_id',
            'catalog_category_entity_varchar' => 'value_id',
            'catalog_category_entity_text' => 'value_id',
            'newsletter_template' => 'template_id',
            'email_template' => 'template_id',
        ];
        $installer = $this->schemaSetup;
        $connection = $this->resource->getConnection();
        foreach ($selectedTables as $tableName => $columnName) {
            $tableName = str_replace("same_", "", $tableName);
            $tableWithPrefix = $connection->getTableName($installer->getTable($tableName));
            $this->resource->getConnection();
            $nameId = $nameIds[$tableName];
            $select = $connection->select()
                ->from(['t' => $tableWithPrefix], ['value' => $columnName, $nameId])
                ->where($columnName . ' LIKE ?', '%{{trans%');
            $data[$tableName] = $connection->fetchAll($select);
            if (!empty($data[$tableName])) {
                foreach ($data[$tableName] as $item) {
                    $value = $item['value'];
                    $matches = [];
                    foreach (['/{{trans\s+"(.*)"/miU', "/{{trans\s+'(.*)'/miU"] as $regex) {
                        preg_match_all($regex, $value, $matches);
                        if (isset($matches[1])) {
                            foreach ($matches[1] as $math) {
                                $source[$math] = $tableName . '/' . $columnName . '/' . $item[$nameId];
                                $strings[] = $math;

                            }
                        }
                    }
                }
            }
        }
        if (!$strings) {
            return [];
        }
        $storeCodes = [];
        $result = [];
        $this->cacheManager->clean(['translate']);
        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            $localeCode = $this->scopeConfig->getValue(
                'general/locale/code',
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
            $localeCode = strtolower($localeCode);
            if (array_key_exists($localeCode, $storeCodes)) {
                continue;
            }
            $storeCodes[$localeCode] = true;

            $this->emulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);
            foreach ($strings as $string) {
                if (!empty($string)) {
                    if (!isset($result[$string])) {
                        $result[$string] = [
                            'string' => $string,
                            'crc_string' => crc32($string),
                            'source' => $source[$string]
                        ];
                    }

                    $result[$string][$localeCode] = (string)__($string);
                    $result[$string][$localeCode . '_translated'] = ($result[$string][$localeCode] == $string) ? 0 : 1;
                }
            }
            $this->emulation->stopEnvironmentEmulation();
        }

        return $result;
    }

    /**
     * Regenerate translations fields
     */
    public function updateData()
    {
        $this->cleanData();

        $installer = $this->schemaSetup;
        $connection = $installer->getConnection();

        try {
            $this->phrasesTranslationsData = $this->phrasesTranslations->getData();

            $items = array_merge(
                $this->phrasesTranslationsData,
                $this->getTablesContent()
                //$this->jsTranslation->getData()
            );

            $this->setUsedArea($items);

            $tableWithPrefix = $connection->getTableName($installer->getTable($this->getResource()->getMainTable()));

            if (!empty($items)) {
                $count = count($items);
                $limit = 1000;
                $offset = 0;
                while ($offset < $count) {
                    $itemsToPush = array_slice($items, $offset, $limit);
                    $connection->insertMultiple($tableWithPrefix, $itemsToPush);
                    $offset += $limit;
                }
            }

            $updatedAt = $this->date->date();
            $this->resourceConfig->saveConfig(
                'mftranslationplus/general/generated_at',
                $updatedAt,
                'default'
            );
            $this->cacheManager->clean(['config']);

        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     * @param $items
     */
    private function setUsedArea(&$items): void
    {
        foreach ($items as $key => $item) {
            $items[$key]['used_in_area'] = UsedInArea::UNDEFINED;

            // since js-translation doesn't have path file, we try to fetch it from collected phrases
            if (!isset($items[$key]['module']) && isset($this->phrasesTranslationsData[$key]['module'])) {
                $items[$key]['module'] = $this->phrasesTranslationsData[$key]['module'];
                $items[$key]['path_to_string'] = $this->phrasesTranslationsData[$key]['path_to_string'];
            }

            if (!isset($items[$key]['module'])) {
                $items[$key]['module'] = 'not detected';
                $items[$key]['path_to_string'] = '';
            } else {
                $paths = explode(PHP_EOL, $items[$key]['path_to_string']);

                $adminhtml = 0;
                $frontend = 0;
                $tests = 0;
                $graphQl = 0;

                foreach ($paths as $path) {
                    if (stripos($path, '/test/') !== false) {
                        $tests++;
                    } elseif (stripos($path, 'graph-ql') !== false
                        || stripos($path, 'GraphQl') !== false
                    ) {
                        $graphQl++;
                    } elseif (stripos($path, '/adminhtml/') !== false
                        || stripos($path, '/backend/') !== false
                        || stripos($path, '/module-backend/') !== false
                        || stripos($path, 'Admin') !== false
                        || stripos($path, '-admin-') !== false
                        || stripos($path, '/Ui/') !== false
                        || stripos($path, '/Model/Config/Source/') !== false
                        || stripos($path, '/etc/') !== false

                        || stripos($path, '/module-aws-s3/') !== false
                        || stripos($path, '/module-backup/') !== false
                        || stripos($path, '/Setup/') !== false
                        
                    ) {
                        $adminhtml++;
                    } elseif (stripos($path, '/model/') !== false) {
                        /** Keep undefined */
                    } elseif (preg_match('%\b(Block|Controller|view|)\b%', $path) > 0) {
                        $frontend++;
                    }
                }

                if ($adminhtml || $frontend) {
                    if ($adminhtml && $frontend) {
                        $items[$key]['used_in_area'] = UsedInArea::STOREFRONT_AND_ADMIN_PANEL;
                    } elseif ($adminhtml) {
                        $items[$key]['used_in_area'] = UsedInArea::ADMIN_PANEL;
                    } else {
                        $items[$key]['used_in_area'] = UsedInArea::STOREFRONT;
                    }
                } elseif ($tests) {
                    $items[$key]['used_in_area'] = UsedInArea::TESTS;
                } elseif ($graphQl) {
                    $items[$key]['used_in_area'] = UsedInArea::GRAPHQL;
                }
            }
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function cleanData()
    {
        $installer = $this->schemaSetup;
        $tableWithPrefix = $installer->getConnection()->getTableName($this->getResource()->getMainTable());

        if ($installer->tableExists($this->getResource()->getMainTable())) {
            $installer->getConnection()->truncateTable($installer->getTable($tableWithPrefix));
        }
    }

    /**
     * @return mixed
     */
    public function getUpdateDataAt()
    {
        return $this->scopeConfig->getValue(
            'mftranslationplus/general/generated_at',
            'default'
        );
    }

    /**
     * @param false $cansel
     * @return $this
     */
    public function scheduleUpdate($schedule = true)
    {
        $this->resourceConfig->saveConfig(
            'mftranslationplus/general/generate_scheduled',
            $schedule ? time() : 0,
            'default'
        );
        $this->cacheManager->clean(['config']);

        return $this;
    }

    /**
     * @return int
     */
    public function getScheduleUpdate()
    {
        return (int)$this->scopeConfig->getValue(
            'mftranslationplus/general/generate_scheduled',
            'default'
        );
    }
}
