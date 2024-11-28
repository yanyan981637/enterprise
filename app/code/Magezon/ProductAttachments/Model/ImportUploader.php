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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;

class ImportUploader extends AbstractUploader
{
    const BASE_TMP_PATH = "productattachments/tmp/import/";
    const BASE_PATH = 'productattachments/files/';

    /**
     * @return string
     */
    public function getBaseTmpPath()
    {
        return self::BASE_TMP_PATH;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return self::BASE_PATH;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->dataHelper->getFileExtension() ? explode(',', $this->dataHelper->getFileExtension()): null;
    }

    /**
     * @param $fileName
     * @param $row
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function moveFileFromTmp($fileName, $row)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();
        $mediaRootDir = $this->mediaDirectory->getAbsolutePath($basePath);
        $name = Uploader::getNewFileName($mediaRootDir . $fileName);
        $baseImagePath = $this->getFilePath($basePath, $name);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $fileName);
        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file(s) Row:' . $row)
            );
        }
        return $name;
    }

    /**
     * @param $fileName
     * @return bool
     */
    public function checkFileExists($fileName)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $mediaRootDir = $this->mediaDirectory->getAbsolutePath($baseTmpPath);
        return $this->_file->isExists($mediaRootDir . $fileName);
    }
}
