<?php
namespace Mitac\Theme\Controller\Adminhtml\Favicon;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;

class Upload extends Action implements HttpPostActionInterface{

    const UPLOAD_DIR = 'wysiwyg/favicon';

    const ADMIN_RESOURCE = 'Magento_Backend::content';

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     * @deprecad use $mediaDirectory instead
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    private $cmsWysiwygImages;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages,
        Filesystem $filesystem = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->directoryList = $directoryList;
        $this->cmsWysiwygImages = $cmsWysiwygImages;
        $filesystem = $filesystem ?? ObjectManager::getInstance()->create(Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function execute() {
        $fieldName = $this->getRequest()->getParam('param_name');
        $fileUploader = $this->uploaderFactory->create(['fileId' => $fieldName]);

        // Set our parameters
        $fileUploader->setFilesDispersion(false);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowedExtensions(['jpeg','jpg','png']);
        $fileUploader->setAllowCreateFolders(true);

        try {
            if (!$fileUploader->checkMimeType(['image/png', 'image/jpeg'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('File validation failed.'));
            }

            $result = $fileUploader->save($this->getUploadDir());
            
            // 添加文件大小检查
            if (!isset($result['file']) || !file_exists($this->getUploadDir() . '/' . $result['file'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('File was not uploaded.'));
            }

            $result['id'] = $this->cmsWysiwygImages->idEncode($result['file']);
            $result['url'] = $this->getMediaUrl(self::UPLOAD_DIR . '/' . $result['file']);
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * Get media URL for uploaded file
     *
     * @param string $path
     * @return string
     */
    private function getMediaUrl($path) 
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
    }

    /**
     * Get absolute path for upload directory
     *
     * @return string
     */
    private function getUploadDir()
    {
        return $this->mediaDirectory->getAbsolutePath(self::UPLOAD_DIR);
    }

}
