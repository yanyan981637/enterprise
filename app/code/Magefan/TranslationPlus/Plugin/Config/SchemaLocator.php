<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Plugin\Config;

use Magento\Framework\Filesystem\Driver\File as DriverFile;
use \Magento\Framework\Filesystem\DirectoryList;

class SchemaLocator
{
    const TEXT_SCHEMA_PATH = 'urn:magento:framework:Setup/Declaration/Schema/etc/types/texts/text.xsd';
    const NEW_TEXT_SCHEMA_PATH = 'urn:magento:module:Magefan_TranslationPlus:etc/text.xsd';

    /**
     * @var DriverFile
     */
    private $driverFile;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param DirectoryList $directoryList
     * @param DriverFile $driverFile
     */
    public function __construct(
        DirectoryList $directoryList,
        DriverFile $driverFile
    ) {
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
    }

    /**
     * After Get Schema
     *
     * @param \Magento\Framework\Config\SchemaLocator $schemaLocator
     * @param string $result
     * @return array
     */
    public function afterGetSchema(\Magento\Framework\Config\SchemaLocator $schemaLocator, $result)
    {
        if (false !== strpos($result, 'schema.xsd')) {
            $schemaContent = $this->driverFile->fileGetContents($result);
            if (false !== strpos($schemaContent, self::TEXT_SCHEMA_PATH)) {
                $newResult = $this->directoryList->getPath('var') . '/' . 'schema_' .  md5($schemaContent) . '.xsd';
                if (!$this->driverFile->isExists($newResult)) {
                    $schemaContent = str_replace(self::TEXT_SCHEMA_PATH, self::NEW_TEXT_SCHEMA_PATH, $schemaContent);
                    $this->driverFile->filePutContents($newResult, $schemaContent);
                }

                return $newResult;
            }
        }
        return $result;
    }
}
