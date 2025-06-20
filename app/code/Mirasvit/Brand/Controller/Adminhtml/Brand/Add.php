<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Controller\Adminhtml\Brand;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Brand\Controller\Adminhtml\Brand;

class Add extends Brand
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if (!$this->config->getGeneralConfig()->getBrandAttribute()) {
            $this->messageManager->addNoticeMessage(
                (string)__('Please add "Brand Attribute" in System->Configuration->MIRASVIT EXTENSION->Brand')
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend((string)__('New Brand'));

        $this->initModel();

        return $resultPage;
    }
}
