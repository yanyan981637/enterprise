<?php
namespace Mitac\Theme\Controller\Adminhtml\Color;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Mitac\Theme\Api\ColorRepositoryInterface;
use Mitac\Theme\Api\Data\ColorInterface;
use Magento\Framework\Registry;
class Edit extends Action
{
    protected $resultPageFactory;
    protected $colorRepository;
    protected $colorModel;
    protected $registry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ColorRepositoryInterface $colorRepository,
        Registry  $registry,
        ColorInterface $colorModel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->colorRepository = $colorRepository;
        $this->registry = $registry;
        $this->colorModel = $colorModel;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('color_id');
        $model = $this->colorModel;
        if($id){
            try {
                $model = $this->colorRepository->getById($id);
                if(!$model->getId()){
                    $this->messageManager->addErrorMessage(__('This color does not exist. ' . $e->getMessage()));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This color does not exist. ' . $e->getMessage()));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }

        }

        // 使用在 data provider 中
        $this->registry->register('current_color', $model);

        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Color') : __('New Color')
        );
        return $resultPage;
    }
}
