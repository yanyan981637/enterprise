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

namespace Magezon\ProductAttachments\Helper;

use Magento\Framework\File\Size;
use Magezon\ProductAttachments\Model\File;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Size
     */
    protected $fileSize;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Size $fileSize
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
        $this->fileSize = $fileSize;
    }

    /**
     * @param string $key
     * @param null|int $_store
     * @return null|string
     */
    public function getConfig($key, $_store = null)
    {
        $store = $this->storeManager->getStore($_store);
        $result = $this->scopeConfig->getValue(
            'productattachments/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $result;
    }

    /**
     * Get status module
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfig('general/enabled');
    }

    /**
     * Get title attachments product tab
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getConfig('product_page/title');
    }

    /**
     * Get status attachments category file
     *
     * @return string|null
     */
    public function isEnabledFileCategory()
    {
        return $this->getConfig('product_page/list_display');
    }

    /**
     * Get Status enable show file icon
     *
     * @return string|null
     */
    public function isEnableFileIcon()
    {
        return $this->getConfig('file_listing/file_icon');
    }

    /**
     * Get Status enable show count download
     *
     * @return string|null
     */
    public function isEnableDownloaded()
    {
        return $this->getConfig('file_listing/downloaded');
    }

    /**
     * Get status enable attach to email
     *
     *
     * @return string|null
     */
    public function isEmailOrder()
    {
        return $this->getConfig('general/email');
    }

    /**
     * Get title attachments order page
     *
     * @return string|null
     */
    public function getTitleForOrder()
    {
        return $this->getConfig('order_page/title');
    }

    /**
     * Get status show file size
     *
     * @return string|null
     */
    public function isEnableFileSize()
    {
        return $this->getConfig('file_listing/file_size');
    }

    /**
     * @param string $type
     * @return bool
     */
    public function getFileExtension()
    {
        return $this->getConfig('general/extension');
    }

    /**
     * Get Position Display
     * @return string|void|null
     */
    public function getPosition()
    {
        if ($this->getConfig('general/enabled')) {
            return $this->getConfig('product_page/position');
        }
        return;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isShowDescription()
    {
        return $this->getConfig('file_listing/description');
    }

    /**
     * @return int
     */
    public function getMaxAttach()
    {
        return (int) $this->getConfig('product_page/show_max');
    }

    /**
     * @return bool
     */
    public function isOrderPage()
    {
        if ($this->getConfig('general/enabled') && $this->getConfig('order_page/enabled')) {
            return true;
        }
        return false;
    }
    /**
     * @return string
     */
    public function getFileHash()
    {
        $fileHash = strtr(
            base64_encode(
                microtime()
            ),
            '+/=',
            '-_,'
        );
        return $fileHash;
    }

    /**
     * Get Url Icon by extension
     *
     * @param $iconCollection
     * @param $extension
     * @return string |null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIcon($iconCollection, $file)
    {
        $icon = $iconCollection->getNewEmptyItem();
        $type = pathinfo($file->getFileType() == File::TYPE_FILE ? $file->getName() : $file->getLink(), PATHINFO_EXTENSION);
        foreach ($iconCollection as $item) {
            if (in_array($type, explode(',', $item->getFileType()))) {
                $icon = $item;
                break;
            }
            if (in_array('default', explode(',', $item->getFileType()))) {
                $icon = $item;
            }
        }
        return $icon;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fieldId, $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fieldId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        }
    }

    /**
     * Get max file size in megabytes
     *
     * @param int $precision
     * @param int $mode
     * @return float
     */
    public function getMaxFileSizeInMb($precision = 0, $mode = \PHP_ROUND_HALF_DOWN)
    {
        return $this->fileSize->getFileSizeInMb($this->getMaxFileSize(), $precision, $mode);
    }

    /**
     * Get maximum upload size message
     *
     * @return \Magento\Framework\Phrase
     */
    public function getMaxUploadSizeMessage()
    {
        $maxImageSize = $this->getMaxFileSizeInMb();
        if ($maxImageSize) {
            $message = __('Make sure your file isn\'t more than %1M.', $maxImageSize);
        } else {
            $message = __('We can\'t provide the upload settings right now.');
        }
        return $message;
    }

    /**
     * Get the maximum file size of the a form in bytes
     *
     * @return integer
     */
    public function getMaxFileSize()
    {
        return $this->fileSize->getMaxFileSize();
    }

    /// magento/framework/File/Mime.php
    public function getMimeTypes()
    {
        return [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
        ];
    }
}
