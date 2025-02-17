<?php
namespace Mitac\Community\Controller\Adminhtml\ImageFile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

use Mitac\Community\Model\FileUploader;

/**
 * Class Upload
 */
class Upload extends Action
{
    private $FileUploader;
    protected $baseTmpPath;
    protected $basePath;
    protected $allowedExtensions;

    /**
     * Upload constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param string $baseTmpPath
     * @param string $basePath
     * @param array $allowedExtensions
     * @param \Mitac\Community\Model\FileUploader $FileUploader
     */
    public function __construct(
        Context $context,
        $baseTmpPath,
        $basePath,
        $allowedExtensions,
        FileUploader $FileUploader
    ) 
    {
        parent::__construct($context);
        $this->FileUploader = $FileUploader;
        $this->baseTmpPath = $baseTmpPath;
        $this->basePath = $basePath;
        $this->allowedExtensions = $allowedExtensions;
    }
    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->FileUploader->saveFileToTmpDir($this->getFieldName(), $this->baseTmpPath, $this->allowedExtensions);

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    protected function getFieldName()
    {
        return $this->_request->getParam('field');
    }
    
}
