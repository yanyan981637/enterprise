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
 * @package   mirasvit/module-search-ultimate
 * @version   2.1.8
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Controller\Adminhtml\ScoreRule;

use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Controller\Adminhtml\AbstractScoreRule;

class Delete extends AbstractScoreRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);

        if ($id) {
            $id = (int) $id;
            try {
                $rule = $this->scoreRuleRepository->get($id);
                $this->scoreRuleRepository->delete($rule);
                $this->messageManager->addSuccessMessage((string)__('The rule was removed'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage((string)__('Please select rule'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
