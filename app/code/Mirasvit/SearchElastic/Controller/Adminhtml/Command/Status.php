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



namespace Mirasvit\SearchElastic\Controller\Adminhtml\Command;

use Mirasvit\SearchElastic\Controller\Adminhtml\Command;

class Status extends Command
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $success = false;
        $message = '';
        $note    = '';

        try {
            if ($this->manager->status($note)) {
                $message = __('Elasticsearch is running.');
                $success = true;
            } else {
                $message = __('Elasticsearch is not running.');
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $jsonData = $this->serializer->serialize([
            'message' => $message,
            'note'    => $note,
            'success' => $success,
        ]);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchElastic::command_status');
    }
}
