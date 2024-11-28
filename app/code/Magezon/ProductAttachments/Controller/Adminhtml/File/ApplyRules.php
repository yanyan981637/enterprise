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

namespace Magezon\ProductAttachments\Controller\Adminhtml\File;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magezon\ProductAttachments\Model\Processor;
use Magezon\ProductAttachments\Model\ResourceModel\File\Collection;

class ApplyRules extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductAttachments::file';

    /**
     * Apply all file rules
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            /** @var Job $ruleJob */
            $ruleJob = $this->_objectManager->get(Processor::class);
            $fileCollection = $this->_objectManager->create(
                Collection::class
            );
            foreach ($fileCollection as $item) {
                $ruleJob->process($item);
            }
            $this->messageManager->addSuccessMessage('Apply rules all done');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
