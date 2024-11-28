<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Model\Import;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magezon\ProductAttachments\Model\Import\ProductAttachments\RowValidatorInterface as ValidatorInterface;
use Magezon\ProductAttachments\Model\ImportUploader as ImportUpload;
use Magezon\ProductAttachments\Model\ResourceModel\File\CollectionFactory as FileCollectionFactory;
use Magezon\ProductAttachments\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magezon\ProductAttachments\Model\File;

class ProductAttachments extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const FILE_ID = 'file_id';
    const FILE_NAME = 'file_name';
    const FILE_LABEL = 'file_label';
    const DESCRIPTION = 'description';
    const FILE_CATEGORY = 'category_id';
    const LINK = 'link';
    const IS_ACTIVE = 'is_active';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const STORE_ID = 'store_id';
    const HASH = 'file_hash';
    const FILE_EXTENSION = 'file_extension';
    const DOWNLOAD_NAME = 'download_name';
    const PRIORITY = 'priority';
    const FILE_TYPE = 'file_type';
    const SKU = 'sku';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const TABLE_ENTITY = 'mgz_product_attachments_file';
    const TABLE_CUSTOMER = 'mgz_product_attachments_customer_group';
    const TABLE_STORE = 'mgz_product_attachments_store';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_MESSAGE_IS_EMPTY => 'Message is empty',
    ];

    /**
     * @var string[]
     */
    protected $_permanentAttributes = [self::FILE_ID];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::FILE_ID,
        self::FILE_NAME,
        self::FILE_LABEL,
        self::LINK,
        self::DESCRIPTION,
        self::FILE_CATEGORY,
        self::IS_ACTIVE,
        self::CUSTOMER_GROUP_ID,
        self::STORE_ID,
        self::SKU,
        self::PRIORITY,
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var array
     */
    protected $_validators = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magezon\ProductAttachments\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var Database
     */
    protected $coreFileStorageDatabase;

    /**
     * @var ImportUpload
     */
    protected $importUpload;

    /**
     * @var FileCollectionFactory
     */
    protected $fileCollection;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $fileCategory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $stores;

    /**
     * @var FileCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroupCollection;

    protected $customerGroupIds;

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magezon\ProductAttachments\Helper\Data $dataHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param FileCollectionFactory $fileCollectionFactory
     * @param ImportUpload $importUpload
     * @param Database $coreFileStorageDatabase
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param array $fileCategory
     * @param array $stores
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magezon\ProductAttachments\Helper\Data $dataHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollection,
        FileCollectionFactory $fileCollectionFactory,
        ImportUpload $importUpload,
        Database $coreFileStorageDatabase,
        ProcessingErrorAggregatorInterface $errorAggregator,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->importUpload = $importUpload;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->dataHelper = $dataHelper;
        $this->fileCollectionFactory = $fileCollectionFactory;
        $this->storeManager = $storeManager;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->customerGroupCollection = $customerGroupCollection;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'magezon_product_attachments';
    }

    /**
     * Get all id category
     * @return array
     */
    public function getFileCategoryIds()
    {
        if ($this->fileCategory == null) {
            $this->fileCategory = $this->categoryCollectionFactory->create()->getAllIds();
        }
        return $this->fileCategory;
    }

    /**
     * Get Store
     * @return array|\Magento\Store\Api\Data\StoreInterface[]
     */
    public function getStores()
    {
        if ($this->stores == null) {
            $storeIds = [];
            $storeIds[] = 0;
            $store = $this->storeManager->getStores();
            foreach ($store as $key => $value) {
                $storeIds[] = $key;
            }
            $this->stores = $storeIds;
        }
        return $this->stores;
    }

    /**
     * Get customer group id
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        if ($this->customerGroupIds == null) {
            $customerGroupIds = [];
            foreach ($this->customerGroupCollection as $customerGroup) {
                $customerGroupIds[] = $customerGroup->getId();
            }
            $this->customerGroupIds = $customerGroupIds;
        }
        return $this->customerGroupIds;
    }
    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $file_id = $rowData['file_id'] ?? '';
        $file_label = $rowData['file_label'] ?? '';
        $file_name = $rowData['file_name'] ?? '';
        $link = $rowData['link'] ?? '';
        $file_category = $rowData['category_id'] ?? '';
        $storeIds = $rowData['store_id'] ?? '';
        $customer_group_ids = $rowData['customer_group_id'] ?? '';

        if (!$file_id) {
            $this->addRowError('File ID is required', $rowNum);
        }
        if ($this->getBehavior() == Import::BEHAVIOR_APPEND) {
            if (!$file_category) {
                $this->addRowError('File Category is required', $rowNum);
            }
            if ($file_category) {
                $listCategory = $this->getFileCategoryIds();
                if (!in_array($file_category, $listCategory)) {
                    $this->addRowError('File Category Does Not Exist', $rowNum);
                }
            }

            if (!$file_label) {
                $this->addRowError('File Label is required', $rowNum);
            }

            if ($link && $file_name) {
                $this->addRowError('Enter either File Name or URL File', $rowNum);
            }
            if (!$link && !$file_name) {
                $this->addRowError('File Name or URL File Null', $rowNum);
            }

            if ($file_name && !$this->importUpload->checkFileExists($file_name)) {
                $this->addRowError('File Name Import Null', $rowNum);
            }

            if ($storeIds && count(array_intersect(explode(',', $storeIds), $this->getStores())) !== count(explode(',', $storeIds))) {
                $this->addRowError('Store ID Does Not Exist', $rowNum);
            }

            if ($customer_group_ids && count(array_intersect(explode(',', $customer_group_ids), $this->getCustomerGroupIds())) !== count(explode(',', $customer_group_ids))) {
                $this->addRowError('Customer Group Id Does Not Exist', $rowNum);
            }
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Create Advanced price data from raw data.
     *
     * @return bool Result of operation.
     * @throws Exception
     */
    protected function _importData()
    {
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveEntity();
        }

        return true;
    }

    /**
     * Save newsletter subscriber
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Deletes newsletter subscriber data from raw data.
     *
     * @return $this
     */
    public function deleteEntity()
    {
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowTtile = $rowData[self::FILE_ID];
                    $listTitle[] = $rowTtile;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listTitle) {
            $this->deleteEntityFinish(array_unique($listTitle), self::TABLE_ENTITY);
        }
        return $this;
    }

    /**
     * Save and replace newsletter subscriber
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceEntity()
    {
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            $customerList = [];
            $storeList = [];
            $schemaEntity = [
                self::FILE_ID,
                self::FILE_NAME,
                self::FILE_LABEL,
                self::FILE_EXTENSION,
                self::LINK,
                self::DOWNLOAD_NAME,
                self::DESCRIPTION,
                self::FILE_CATEGORY,
                self::IS_ACTIVE,
                self::HASH,
                self::PRIORITY,
                self::FILE_TYPE,
                self::CONDITIONS_SERIALIZED,
            ];
            $schemaCustomer = [
                self::FILE_ID,
                self::CUSTOMER_GROUP_ID,
            ];
            $schemaStore = [
                self::FILE_ID,
                self::STORE_ID,
            ];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_INVALID_TITLE, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowTtile = $rowData[self::FILE_ID];
                $listTitle[] = $rowTtile;
                $newFileName = '';
                if ($rowData[self::FILE_NAME]) {
                    $newFileName = $this->importUpload->moveFileFromTmp($rowData[self::FILE_NAME], $rowNum);
                }
                $entityList[$rowTtile][] = [
                    self::FILE_ID => $rowData[self::FILE_ID],
                    self::FILE_NAME => $newFileName,
                    self::FILE_LABEL => $rowData[self::FILE_LABEL],
                    self::FILE_EXTENSION => $rowData[self::FILE_NAME] ? pathinfo($rowData[self::FILE_NAME], PATHINFO_EXTENSION) : pathinfo($rowData[self::LINK], PATHINFO_EXTENSION),
                    self::LINK => $rowData[self::LINK],
                    self::DOWNLOAD_NAME => $rowData[self::FILE_NAME] ? pathinfo($rowData[self::FILE_NAME], PATHINFO_FILENAME) : pathinfo($rowData[self::LINK], PATHINFO_FILENAME),
                    self::DESCRIPTION => $rowData[self::DESCRIPTION],
                    self::FILE_CATEGORY => $rowData[self::FILE_CATEGORY],
                    self::IS_ACTIVE => $rowData[self::IS_ACTIVE],
                    self::HASH => $this->dataHelper->getFileHash(),
                    self::PRIORITY => $rowData[self::PRIORITY],
                    self::FILE_TYPE => $rowData[self::FILE_NAME] ? File::TYPE_FILE : File::TYPE_URL,
                    self::CONDITIONS_SERIALIZED => $rowData[self::SKU] ? $this->getConditionSerialized($rowData[self::SKU]) : ''
                ];

                if (isset($rowData[self::CUSTOMER_GROUP_ID])) {
                    foreach (explode(',', $rowData[self::CUSTOMER_GROUP_ID]) as $id) {
                        $customerList[$rowTtile][] = [
                            self::FILE_ID => $rowData[self::FILE_ID],
                            self::CUSTOMER_GROUP_ID => $id,
                        ];
                    }
                } else {
                    $customerList[$rowTtile][] = [
                        self::FILE_ID => $rowData[self::FILE_ID],
                        self::CUSTOMER_GROUP_ID => 0,
                    ];
                }
                if (isset($rowData[self::STORE_ID])) {
                    foreach (explode(',', $rowData[self::STORE_ID]) as $id) {
                        $storeList[$rowTtile][] = [
                            self::FILE_ID => $rowData[self::FILE_ID],
                            self::STORE_ID => $id,
                        ];
                    }
                } else {
                    $storeList[$rowTtile][] = [
                        self::FILE_ID => $rowData[self::FILE_ID],
                        self::STORE_ID => 0,
                    ];
                }
            }
            if ($listTitle) {
                $this->saveEntityFinish($entityList, self::TABLE_ENTITY, $schemaEntity);
                $this->saveEntityFinish($customerList, self::TABLE_CUSTOMER, $schemaCustomer);
                $this->saveEntityFinish($storeList, self::TABLE_STORE, $schemaStore);
            }
        }
        return $this;
    }

    /**
     * Save product prices.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveEntityFinish(array $entityData, $table, $schema)
    {
        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {
                foreach ($entityRows as $row) {
                    $entityIn[] = $row;
                }
            }
            if ($entityIn) {
                $this->_connection->insertOnDuplicate($tableName, $entityIn, $schema);
            }
        }
        return $this;
    }

    protected function deleteEntityFinish(array $listTitle, $table)
    {
        if ($table && $listTitle) {
            try {
                $fileCollection = $this->fileCollectionFactory
                    ->create()
                    ->addFieldToFilter('main_table.file_id', ['in' => $listTitle]);
                foreach ($fileCollection as $file) {
                    if ($file->getType() == File::TYPE_FILE) {
                        $this->importUpload->deleteImage($file->getName());
                    }
                }
                $this->countItemsDeleted += $this->_connection->delete(
                    $this->_connection->getTableName($table),
                    $this->_connection->quoteInto('file_id IN (?)', $listTitle)
                );
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $sku
     * @return string
     */
    protected function getConditionSerialized($sku)
    {
        return '{"type":"Magento\\\CatalogRule\\\Model\\\Rule\\\Condition\\\Combine","attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all","conditions":[{"type":"Magento\\\CatalogRule\\\Model\\\Rule\\\Condition\\\Product","attribute":"sku","operator":"()","value":"'.$sku.'","is_value_processed":false}]}';
    }
}
