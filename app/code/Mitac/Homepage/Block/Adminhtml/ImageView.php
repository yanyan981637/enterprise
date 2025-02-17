<?php
namespace Mitac\Homepage\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImageView extends Template
{
    protected $_template = 'template.phtml';
    protected $helper;
    protected $storeManager;
    protected $file;
    protected $filesystem;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mitac\Homepage\Helper\ImageData $helper,
        File $file,
        Filesystem $filesystem
    )
    {
        $this->_helper = $helper;
        $this->baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $this->mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }

    public function getImagerUrl($bannerId)
    {
      $BlockData = $this->_helper->getImgData($bannerId);

      $slideHTML = '';

      if(!empty($BlockData[0]['img']))
      {
        $TempmediaRootDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $mediaRootDir = $TempmediaRootDir.'Image/Mitac/Homepage/ImageUpload/';
        if ($this->_file->isExists($mediaRootDir.$BlockData[0]['img']))
        {
          $slideHTML .= '<div style="text-align:left;">
                          <img width="30%" height="30%" src="'.$this->mediaUrl.'Image/Mitac/Homepage/ImageUpload/'.$BlockData[0]['img'].'"/>
                        </div>';
        }
        else {
          $slideHTML .= '<div style="text-align:left;">
                          Imager is not found!!
                        </div>';
        }
      }

      return $slideHTML;
    }

    public function getImagerButton($bannerId)
    {
      $BlockData = $this->_helper->getImgData($bannerId);

      $slideHTML = '';

      if(!empty($BlockData[0]['img']))
      {
        $slideHTML .= '<div style="text-align:left;">
                          <button name="emq_zip_btn" class="emq_zip_btn">Remove Image</button>
                      </div>';
      }

      return $slideHTML;
    }

}
