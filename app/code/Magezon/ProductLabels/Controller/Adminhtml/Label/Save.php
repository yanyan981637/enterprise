<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2021 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filter\FilterInput;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_ProductLabels::label_save';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magezon\ProductLabels\Model\Processor
     */
    protected $processor;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @param Action\Context                           $context     
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder 
     * @param \Magezon\ProductLabels\Model\Processor   $processor   
     * @param \Magento\Framework\App\Request\Http      $httpRequest 
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magezon\ProductLabels\Model\Processor $processor,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
        ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->processor   = $processor;
        $this->httpRequest = $httpRequest;
        $this->dateFilter = $dateFilter;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
    
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            /** @var \Magezon\ProductLabels\Model\Label $model */
            $model = $this->_objectManager->create('Magezon\ProductLabels\Model\Label');

            $id = $this->getRequest()->getParam('label_id');
            if ($id) {
                $model->load($id);
            }

            if (isset($data['rule'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
            }
            unset($data['conditions_serialized']);
            unset($data['actions_serialized']);

            $filterValues = [];
            if ($data['from_date']) {
                $filterValues['from_date'] = $this->dateFilter;
            }
            if ($this->getRequest()->getParam('to_date')) {
                $filterValues['to_date'] = $this->dateFilter;
            }
            if ($filterValues) {
                $inputFilter = new FilterInput(
                    $filterValues,
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
            } else {
                $data['from_date'] = $data['to_date'] = null;
            }

            if ($data['priority'] <= 0) {
                $data['priority'] = 1;
            } 
            $model->loadPost($data);
            $model->save();
            try {
                $this->messageManager->addSuccess(__('You saved the label.'));
                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->processor->process($model);
                    return $resultRedirect->setPath('*/*/edit', ['label_id' => $model->getId(), '_current' => true]);
                }
                if ($this->getRequest()->getParam('save_and_continue')) {
                    return $resultRedirect->setPath('*/*/edit', ['label_id' => $model->getId(), '_current' => true]);
                }
                if ($this->getRequest()->getParam('reset_button')) {
                    return $resultRedirect->setPath('*/*/newaction');
                }
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['label_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the label.'));
            }

            return $resultRedirect->setPath('*/*/edit', ['label_id' => $this->getRequest()->getParam('label_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

}
