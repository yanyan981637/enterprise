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
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Image extends AbstractHelper
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_filesystem   = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $src
     * @param $width
     * @param $height
     * @param $quality
     * @param $dir
     * @param $attributes
     * @return string
     * @throws NoSuchEntityException
     */
    public function resize($src, $width = 150, $height = 0, $quality = 100, $dir = 'magezon/resized', $attributes = [])
    {
        $dir = $dir . '/' . $width;
        if ($height) $dir .= 'x' . $height;
        $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $absPath = $mediaDir->getAbsolutePath($src);
        $imageResized = $mediaDir->getAbsolutePath($dir . '/' . $src);    
        $imageResize  = $this->_imageFactory->create(); 
        $resizedURL   = '';
        if (file_exists($absPath)) {
            $imageResize->open($absPath);
            $imageResize->backgroundColor([255, 255, 255]);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepAspectRatio((isset($attributes['keepAspectRatio']) ? $attributes['keepAspectRatio'] : true));
            if ($height) $imageResize->keepFrame(true);
            $imageResize->quality($quality);
            if ($height) {
                $imageResize->resize($width, $height);    
            } else {
                $imageResize->resize($width);
            }
            $imageResize->save($imageResized);
            $resizedURL = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $dir . '/' . $src;
        }
        return $resizedURL;
    }
}
