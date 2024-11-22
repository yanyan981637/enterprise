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


namespace Mirasvit\LayeredNavigation\Controller\Adminhtml\Group;


use Magento\Framework\Controller\ResultFactory;
use Mirasvit\LayeredNavigation\Controller\Adminhtml\Group;

class Index extends Group
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()
            ->getTitle()
            ->prepend((string)__('Grouped Options'));

        return $resultPage;
    }
}
