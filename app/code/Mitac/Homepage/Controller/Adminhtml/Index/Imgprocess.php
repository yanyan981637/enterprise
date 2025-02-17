<?php
 namespace Mitac\Homepage\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class imgprocess extends Action 
{

  protected $filesystem;
  protected $file;

  public function __construct(
      Context $context,
      \Mitac\Homepage\Helper\ImageData $helper,
      Filesystem $filesystem,
      File $file
  )
  {
      parent::__construct($context);
      $this->_helper = $helper;
      $this->_filesystem = $filesystem;
      $this->_file = $file;
  }

  public function execute()
  {
    $id = $this->getRequest()->getParam('BannersId');

    if (isset($id))
    {
      $BlockData = $this->_helper->getImgData($id);
      
      if(isset($BlockData[0]['img']))
      {
        $filename = $BlockData[0]['img'];
        $TempmediaRootDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $mediaRootDir = $TempmediaRootDir.'Image/Mitac/Homepage/ImageUpload/';

        if ($this->_file->isExists($mediaRootDir.$filename))
        {
          $DelResult = $this->_file->deleteFile($mediaRootDir.$filename);
          if($DelResult==true)
          {
            $this->_helper->voidImgData($id);
            $state = 'success';
          }
          else 
          {
            $state = 'error';
          }
        }
        else 
        {
          $this->_helper->voidImgData($id);
          $state = 'success';
        }
      }
      else 
      {
        $state = 'error';
      }
    }
    else 
    {
      $state = 'error';
    }

    $data = array('state' => $state);
    $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
    $resultJson->setData($data);

    return $resultJson;
  }
}
