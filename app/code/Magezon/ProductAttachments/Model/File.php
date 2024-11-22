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

namespace Magezon\ProductAttachments\Model;

use Magento\CatalogRule\Model\Rule\Action\Collection;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\System\Ftp;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Context as UrlBuilder;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Condition\Combine;
use Magezon\ProductAttachments\Api\Data\FileInterface;
use Magezon\ProductAttachments\Model\FileUploader;

class File extends AbstractModel implements FileInterface
{
    /**
     * File statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const ATTACH_EMAIL_ENABLED = 1;
    const ATTACH_EMAIL_DISABLED = 0;
    const TYPE_FILE = 'file';
    const TYPE_URL = 'url';
    const DOWNLOAD_URL = 'productattachment/file/download';

    /**
     * @var Data\Condition\Converter
     */
    protected $ruleConditionConverter;

    /**
     * @var CombineFactory
     */
    protected $_combineFactory;

    /**
     * @var CollectionFactory
     */
    protected $_actionCollectionFactory;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * File constructor.
     * @param Filesystem $filesystem
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $combineFactory
     * @param CollectionFactory $actionCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param UrlBuilder $urlBuilder
     * @param \Magezon\Core\Helper\Data $coreHelper
     * @param \Magezon\ProductAttachments\Helper\Data $dataHelper
     * @param array $relatedCacheTypes
     * @param array $data
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CombineFactory $combineFactory,
        CollectionFactory $actionCollectionFactory,
        UrlBuilder $urlBuilder,
        \Magezon\Core\Helper\Data $coreHelper,
        \Magezon\ProductAttachments\Helper\Data $dataHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->urlBuilder = $urlBuilder->getUrlBuilder();
        $this->coreHelper = $coreHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\File::class);
    }

    /**
     * Getter for rule conditions collection
     *
     * @return Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::FILE_ID);
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get file label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * Get file description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get file category id
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * Get file url
     *
     * @return string
     */
    public function getLink()
    {
        return $this->getData(self::LINK);
    }

    /**
     * Get file status
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Get download limit file
     *
     * @return int
     */
    public function getDownloadLimit()
    {
        return $this->getData(self::DOWNLOAD_LIMIT);
    }

    /**
     * Get status file email
     *
     * @return bool
     */
    public function getAttachToEmail()
    {
        return $this->getData(self::ATTACH_TO_EMAIL);
    }

    /**
     * Get download type
     *
     * @return int
     */
    public function getDownloadType()
    {
        return $this->getData(self::DOWNLOAD_TYPE);
    }

    /**
     * Get create time file
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getFileExtention()
    {
        return $this->getData(self::FILE_EXTENSION);
    }

    /**
     * Get file hash
     *
     * @return string
     */
    public function getFileHash()
    {
        return parent::getData(self::FILE_HASH);
    }

    /**
     * Get pload type
     *
     * @return string
     */
    public function getType()
    {
        return parent::getData(self::FILE_TYPE);
    }

    /**
     * Get is buyer
     *
     * @return bool
     */
    public function getIsBuyer()
    {
        return parent::getData(self::IS_BUYER);
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return parent::getData(self::CONTENT);
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContents($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get status file order
     *
     * @return bool
     */
    public function getAttachToOrder()
    {
        return parent::getData(self::ATTACH_TO_ORDER);
    }

    /**
     * Set is buyer
     *
     * @param bool $isBuyer
     * @return $this
     */
    public function setIsBuyer($isBuyer)
    {
        return $this->setData(self::IS_BUYER, $isBuyer);
    }

    /**
     * Set file hash
     *
     * @param string $hash
     * @return $this
     */
    public function setFileHash($hash)
    {
        return $this->setData(self::FILE_HASH, $hash);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::FILE_ID, $id);
    }

    /**
     * Set pload type
     *
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::FILE_TYPE, $type);
    }

    /**
     * Set file name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set label file
     *
     * @param string $label
     * @return File
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * Set file description
     *
     * @param string $description
     * @return File
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set file category id
     *
     * @param int $id
     * @return File
     */
    public function setCategoryId($id)
    {
        return $this->setData(self::CATEGORY_ID, $id);
    }

    /**
     * Set file url
     *
     * @param string $link
     * @return File
     */
    public function setLink($link)
    {
        return $this->setData(self::LINK, $link);
    }

    /**
     * Set file status
     *
     * @param bool $status
     * @return File
     */
    public function setIsActive($status)
    {
        return $this->setData(self::IS_ACTIVE, $status);
    }

    /**
     * Set download limit/default 0
     *
     * @param int $number
     * @return File
     */
    public function setDownloadLimit($number)
    {
        return $this->setData(self::DOWNLOAD_LIMIT, $number);
    }

    /**
     * Set status attach to email
     *
     * @param bool $status
     * @return File
     */
    public function setAttachToEmail($status)
    {
        return $this->setData(self::ATTACH_TO_EMAIL, $status);
    }

    /**
     * Set status attach to order
     *
     * @param bool $status
     * @return File
     */
    public function setAttachToOrder($status)
    {
        return $this->setData(self::ATTACH_TO_ORDER, $status);
    }

    /**
     * Set create time
     *
     * @param string $time
     * @return File
     */
    public function setCreationTime($time)
    {
        return $this->setData(self::CREATION_TIME, $time);
    }

    /**
     * Set update time
     *
     * @param string $time
     * @return File
     */
    public function setUpdateTime($time)
    {
        return $this->setData(self::UPDATE_TIME, $time);
    }

    /**
     * Set download type
     *
     * @param int $type
     * @return File
     */
    public function setDownloadType($type)
    {
        return $this->setData(self::DOWNLOAD_TYPE, $type);
    }

    /**
     * Get download name
     *is
     * @return string
     */
    public function getDownloadName()
    {
        return $this->getData(self::DOWNLOAD_NAME) . '.' . $this->getFileExtention();
    }

    /**
     * Set download name
     *
     * @param string $name
     * @return $this
     */
    public function setDownloadName($name)
    {
        return $this->setData(self::DOWNLOAD_NAME, $name);
    }

    /**
     * Get conditions field set id.
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get actions field set id.
     *
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFileUrl()
    {
        if ($this->getType() == self::TYPE_FILE) {
            return $this->coreHelper->getMediaUrl() . FileUploader::BASE_PATH . $this->getName();
        }
        return $this->getLink();
    }

    /**
     * Get absolute path file attach
     *
     * @return string
     */
    public function getAbsolutePathFile()
    {
        $mediaRootDir = $this->mediaDirectory->getAbsolutePath(FileUploader::BASE_PATH);
        if ($this->getType() == self::TYPE_FILE) {
            return $mediaRootDir . $this->getName();
        }
        return $this->getLink();
    }

    /**
     * Get download url
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->urlBuilder->getUrl(self::DOWNLOAD_URL, ['id' => $this->getFileHash()]);
    }

    /**
     * Get file size
     *
     * @return string
     */
    public function getFileSize()
    {
        try {
            $size = null;
            if ($this->getType() == self::TYPE_FILE) {
                $size = $this->mediaDirectory->stat($this->getAbsolutePathFile())['size'];
            } else {
                $ch = curl_init($this->getAbsolutePathFile());
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_exec($ch);
                $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($responseCode == 200) {
                    $size = strlen(file_get_contents($this->getAbsolutePathFile()));
                }
            }
        } catch (\Exception $e) {
        }
        return $size ? Ftp::byteconvert($size) : null;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        $mimeTypes = $this->dataHelper->getMimeTypes();
        return isset($mimeTypes[$this->getFileExtension()]) ? $mimeTypes[$this->getFileExtension()] : '';
    }
}
