<?php
namespace Mitac\Theme\Controller\Adminhtml\Color;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mitac\Theme\Api\ColorRepositoryInterface;

class Delete extends Action
{
    protected $colorRepository;

    public function __construct(
        Context $context,
        ColorRepositoryInterface $colorRepository,
    ) {
        parent::__construct($context);
        $this->colorRepository = $colorRepository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('color_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $model = $this->colorRepository->getById($id);
                $this->colorRepository->delete($model);
                $this->messageManager->addSuccessMessage(__('The color has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a color to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
