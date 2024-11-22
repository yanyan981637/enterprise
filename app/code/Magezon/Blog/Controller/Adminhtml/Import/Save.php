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

namespace Magezon\Blog\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magezon\Blog\Model\Import\AheadworksFactory;
use Magezon\Blog\Model\Import\AmastyFactory;
use Magezon\Blog\Model\Import\MageFanFactory;
use Magezon\Blog\Model\Import\MagePlazaFactory;
use Magezon\Blog\Model\Import\WordpressFactory;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_Blog::import';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var AheadworksFactory
     */
    protected $aheadworksFactory;

    /**
     * @var AmastyFactory
     */
    protected $amastyFactory;

    /**
     * @var MageFanFactory
     */
    protected $magefanFactory;

    /**
     * @var MagePlazaFactory
     */
    protected $mageplazaFactory;

    /**
     * @var WordpressFactory
     */
    protected $wordpressFactory;


    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        AheadworksFactory $aheadworksFactory,
        AmastyFactory $amastyFactory,
        MageFanFactory $magefanFactory,
        MagePlazaFactory $mageplazaFactory,
        WordpressFactory $wordpressFactory
    ) {
        $this->dataPersistor     = $dataPersistor;
        $this->aheadworksFactory = $aheadworksFactory;
        $this->amastyFactory     = $amastyFactory;
        $this->magefanFactory    = $magefanFactory;
        $this->mageplazaFactory  = $mageplazaFactory;
        $this->wordpressFactory  = $wordpressFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
                switch ($data['type']) {

                    case 'aheadworks':
                        $import = $this->aheadworksFactory->create();
                        break;

                    case 'amasty':
                        $import = $this->amastyFactory->create();
                        break;

                    case 'magefan':
                        $import = $this->magefanFactory->create();
                        break;

                    case 'mageplaza':
                        $import = $this->mageplazaFactory->create();
                        break;

                    case 'wordpress':
                        $import = $this->wordpressFactory->create();
                        break;
                }
                if (isset($import)) {
                    $import->addData($data);
                    $import->import();
                    $this->messageManager->addSuccess(__(
                        'Import successfully done. %1 posts, %2 categories, %3 tags and %4 comments where imported.',
                        count($import->getImportedPosts()),
                        count($import->getImportedCategories()),
                        count($import->getImportedTags()),
                        count($import->getImportedComments())
                    ));
                    $this->dataPersistor->clear('blog_import');
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?:$e);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while process the request.'));
            }
        }
        $this->dataPersistor->set('blog_import', $data);
        return $resultRedirect->setPath('*/*/');
    }
}
