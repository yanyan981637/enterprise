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



namespace Mirasvit\Search\Controller\Adminhtml\Synonym;

use Mirasvit\Search\Controller\Adminhtml\AbstractSynonym;

class DoImport extends AbstractSynonym
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $generator = $this->synonymService->import($data['dictionary'], $data['store_id']);

            $result = [
                'synonyms' => 0,
                'errors'   => 0,
                'message'  => '',
            ];

            foreach ($generator as $result) {
            }

            if ($result['synonyms'] > 0) {
                $this->messageManager->addSuccessMessage((string)__('Imported %1 synonym(s).', $result['synonyms']));
            }

            if ($result['errors']) {
                if (empty($result['message'])) {
                    $this->messageManager->addWarningMessage((string)__('%1 errors.', $result['errors']));
                } else {
                    $this->messageManager->addWarningMessage((string)__('%1', $result['message']));
                }
            }

        } else {
            $this->messageManager->addErrorMessage('No data to import.');
        }

        return $resultRedirect->setPath('search/synonyms/');
    }
}
