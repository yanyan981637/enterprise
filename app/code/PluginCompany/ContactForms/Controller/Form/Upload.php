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

namespace PluginCompany\ContactForms\Controller\Form;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use PluginCompany\ContactForms\Model\FormRepository;

class Upload extends Action
{

    protected $resultPageFactory;
    protected $jsonHelper;

    private $ioFile;
    private $directoryList;
    private $uploaderFactory;
    private $customerSession;
    private $logger;
    private $formRepository;

    private $fileId = 'file';
    private $allowedExtensions = ['csv', 'jpeg', 'jpg', 'zip', 'txt'];


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param File $ioFile
     * @param UploaderFactory $uploaderFactory
     * @param Session $customerSession
     * @param LoggerInterface $logger
     * @param FormRepository $formRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $jsonHelper,
        DirectoryList $directoryList,
        File $ioFile,
        UploaderFactory $uploaderFactory,
        Session $customerSession,
        LoggerInterface $logger,
        FormRepository $formRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        $this->uploaderFactory = $uploaderFactory;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->formRepository = $formRepository;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->saveUploadedFileIfAllowed();
        } catch (LocalizedException $e) {
            $this->getResponse()
                ->setHttpResponseCode(500);
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->getResponse()
                ->setHttpResponseCode(500);
            return $this->jsonResponse($e->getMessage());
        }
    }

    public function saveUploadedFileIfAllowed()
    {
        $this->checkIsUploadAllowed();
        $this->saveUploadedFile();

        return $this;
    }

    public function checkIsUploadAllowed()
    {
        if(!$this->isFileSizeOk()){
            throw new LocalizedException(__('File is too big'));
        }
        return $this;
    }

    public function isFileCountExceeded()
    {
        $this->getUploadedFileCount();
        return $this;
    }

    public function getUploadedFileCount()
    {
        $dir = $this->getUploadDirIO();
        return count($dir->ls());
    }

    public function getUploadDirIO()
    {
        $io = $this->getIoFile();
        $io->checkAndCreateFolder($this->getUploadDir());
        $io->cd($this->getUploadDir());
        return $io;
    }

    public function saveUploadedFile()
    {
        if(!$this->getFormId()){
            $this->throwNoFormIdException();
        }
        $uploader = $this->getNewUploader();
        if (!$uploader->save($this->getUploadDir())) {
            $this->throwSaveError();
        }
        return $this;
    }

    public function getFormId()
    {
        return $this->_request->getParam('form_id');
    }

    public function throwNoFormIdException()
    {
        throw new LocalizedException(
            __('No Form ID specified')
        );
    }

    public function getNewUploader()
    {
        return $this->uploaderFactory
            ->create(['fileId' => $this->fileId])
            ->setAllowCreateFolders(true)
            ->setAllowedExtensions($this->getAllowedExtensions())
            ->addValidateCallback('checkfilesize', $this, 'validateFileSize')
            ;
    }

    public function getAllowedExtensions()
    {
        $uploadElement = $this->getUploadElement();
        $extensions = $uploadElement['fields']['extensions']['value'];
        if(empty($extensions)) {
            return null;
        }
        return explode(',',$extensions);
    }

    public function getUploadElement()
    {
        $uploadElements = $this->getUploadElements();
        $index = $this->getUploadElementIndex();
        return $uploadElements[$index];
    }

    private function getUploadElements()
    {
        return $this->getForm()->getUploadElements();
    }

    public function getForm()
    {
        return $this->formRepository
            ->getById($this->getFormId());
    }

    private function getUploadElementIndex()
    {
        return $this->_request->getParam('upload_element_index');
    }


    public function getUploadDir()
    {
        if(!$this->getUploadDirFromSession() || !$this->uploadDirMatchesUniqueFormInstanceId()){
            $this->initSessionUploadDir();
        }
        return $this->getUploadDirFromSession();
    }

    private function getUploadDirFromSession()
    {
        return $this->customerSession
            ->getData($this->getUploadDirSessionDataKey());
    }

    private function uploadDirMatchesUniqueFormInstanceId()
    {
        return (bool) stristr($this->getUploadDirFromSession(), $this->getUniqueFormInstanceId());
    }

    private function getUniqueFormInstanceId()
    {
        return $this->getRequest()->getParam('unique_form_instance');
    }

    private function initSessionUploadDir()
    {
        $this->customerSession
            ->setData($this->getUploadDirSessionDataKey(), $this->getNewUploadDir());
        return $this;
    }

    private function getNewUploadDir()
    {
        return $this->getUploadBaseDir() . $this->getUploadSubDir();
    }

    private function getUploadDirSessionDataKey()
    {
        return 'contactforms_upload_dir_' . $this->getFormId();
    }

    private function getUploadBaseDir()
    {
        $ds = DIRECTORY_SEPARATOR;
        return $this->getMediaDir() . $ds . 'contactforms' . $ds . 'uploads' . $ds;
    }

    private function getMediaDir()
    {
        return $this->getDirectoryList()->getPath('media');
    }

    private function getUploadSubDir()
    {
        return $this->getUniqueUploadKey() . DIRECTORY_SEPARATOR;
    }

    private function getUniqueUploadKey()
    {
        $session = $this->customerSession;
        $key = $session->getData($this->getSessionDataKeyForForm());
        if(!$key || !$this->uploadDirMatchesUniqueFormInstanceId()){
            $key = uniqid($this->getFormId() . '_') . $this->getUniqueFormInstanceId();
            $session->setData($this->getSessionDataKeyForForm(), $key);
        }
        return $key;
    }

    private function getSessionDataKeyForForm()
    {
        return 'contactforms_upload_key_' . $this->getFormId();
    }

    public function throwSaveError()
    {
        throw new LocalizedException(
            __('File cannot be saved')
        );
    }

    public function isFileSizeOk()
    {
        $maxSize = $this->getMaxAllowedFileSize();
        if($maxSize && $maxSize < $this->getUploadedFileSize()){
            return false;
        }
        return true;
    }

    public function getMaxAllowedFileSize()
    {
        $e = $this->getUploadElement();
        return $e['fields']['max_filesize']['value'];
    }

    private function getUploadedFileSize()
    {
        $files = $this->_request->getFiles();
        foreach($files as $file){
            $size = $file['size'] / (1024 * 1024);
            return $size;
        }
    }

    /**
     * Create json response
     *
     * @param string $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * @return \Magento\Framework\Filesystem\Io\File
     */
    public function getIoFile()
    {
        return $this->ioFile;
    }

    /**
     * @return \Magento\Framework\Filesystem\DirectoryList
     */
    public function getDirectoryList()
    {
        return $this->directoryList;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
