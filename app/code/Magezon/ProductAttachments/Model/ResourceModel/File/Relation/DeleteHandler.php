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

namespace Magezon\ProductAttachments\Model\ResourceModel\File\Relation;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magezon\ProductAttachments\Model\FileUploader;

/**
 * Class DeleteHandler
 */
class DeleteHandler implements ExtensionInterface
{
    /**
     * @var FileUploader
     */
    protected $fileUploader;

    /**
     * DeleteHandler constructor.
     *
     * @param FileUploader $fileUploader
     */
    public function __construct(
        FileUploader $fileUploader
    ) {
        $this->fileUploader = $fileUploader;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getFileType() != \Magezon\ProductAttachments\Model\File::TYPE_FILE) {
            return $entity;
        }
        try {
            $this->fileUploader->deleteImage($entity->getName());
        } catch (\Exception $e) {
        }
        return $entity;
    }
}