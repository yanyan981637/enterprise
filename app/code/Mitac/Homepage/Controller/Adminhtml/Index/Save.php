<?php
namespace Mitac\Homepage\Controller\Adminhtml\Index;

use Throwable;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Api\BlockRepositoryInterface;
use Mitac\Homepage\Model\Block;
use Mitac\Homepage\Model\BlockFactory;
use Mitac\Homepage\Helper\StoreData;
use Mitac\Community\Model\FileUploader;

class Save extends Action
{
    private $blockRepository;
    private $blockFactory;
    protected $dataObjectHelper;
    protected $uploaderPool;
    protected $baseTmpPath;
    protected $basePath;
    protected $StroeHelper;

    /**
     * @param Action\Context $context
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        Action\Context $context,
        BlockRepositoryInterface $blockRepository,
        DataObjectHelper $dataObjectHelper,
        FileUploader $fileuploader,
        $baseTmpPath,
        $basePath,
        BlockFactory $blockFactory,
        StoreData $storehelper
    ) 
    {
        parent::__construct($context);
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->dataObjectHelper  = $dataObjectHelper;
        $this->uploaderPool = $fileuploader;
        $this->baseTmpPath = $baseTmpPath;
        $this->basePath = $basePath;
        $this->StroeHelper = $storehelper;
    }

    /**
     * @return ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        //------------------------------------------------------------------//
        $id             = $this->getRequest()->getParam('banners_id');
        $stores_id      = $this->getRequest()->getParam('stores_id');
        $PageIdentifier = $this->getRequest()->getParam('PageIdentifier');
        $created_at     = $this->getRequest()->getParam('created_at');
        $data           = $this->getRequest()->getParams();
        //echo '<pre>', print_r($data, true), '</pre>'; 
        
        if(!empty($created_at))
        {
            $created_at = str_replace('/', '-', $created_at);
            $created_at = date("Y-m-d", strtotime($created_at));
        }
        else
        {
            $created_at = "";
        }
        unset($data[BlockInterface::KEY_CREATED_AT]);
        $data['created_at'] = $created_at;
        //------------------------------------------------------------------//
        //Store Option Value Get
        unset($data[BlockInterface::KEY_STORES_ID]);
        $data['stores_id'] = $stores_id[0];

        if (!empty($PageIdentifier)) 
        {
            $PageIdentifierArr = explode('<=>',$PageIdentifier);
            if(!empty($PageIdentifierArr[1]))
            {
                $data['cms_page_id'] = $PageIdentifierArr[0];
                $data['PageIdentifier'] = $PageIdentifierArr[1];
            }
        }
        //------------------------------------------------------------------//
        //Save Data Process
        /** @var Block $block */
        if ($id) 
        {
            $block = $this->blockRepository->getById($id);
        }
        else 
        {
            unset($data[BlockInterface::KEY_ID]);
            $block = $this->blockFactory->create();
        }
        //------------------------------------------------------------------//
        $image = $this->uploaderPool->uploadFileAndGetName('img', $data, $this->baseTmpPath, $this->basePath);
        $data['img'] = $image;

        unset($data[BlockInterface::KEY_UPDATED_AT]);
        //------------------------------------------------------------------//
        $block->setData($data);

        $this->dataObjectHelper->populateWithArray($block, $data, BlockInterface::class);

        if ($data['type'] == 'block')
        {
            $data['type'] = 'index';
        }

        $returnUri = $data['type'];
        
        try
        {
            $this->blockRepository->save($block);
            $this->messageManager->addSuccessMessage(__('Block saved successfully'));

            $this->StroeHelper->deleteBannerStore($block->getId());
            $this->StroeHelper->InsertBannerStore($block->getId(), $stores_id);

            if (!empty($PageIdentifier))
            {
                $PageIdentifierArr = explode('<=>',$PageIdentifier);
                if(!empty($PageIdentifierArr[1]))
                {
                    $PageIdentifierUrl = $PageIdentifierArr[1];
                    $this->StroeHelper->deletePageIdenifier($block->getId());
                    $this->StroeHelper->InsertPageIdenifier($block->getId(), $PageIdentifierUrl);
                }
            }
            
            if (key_exists('back', $data) && $data['back'] == 'edit') 
            {
                return $resultRedirect->setPath('*/'.$returnUri.'/edit', ['id' => $id, '_current' => true]);
            }

            return $resultRedirect->setPath('*/'.$returnUri.'/');
        } 
        catch (Throwable $throwable) 
        {
            $this->messageManager->addErrorMessage(__("Block not saved"));
            return $resultRedirect->setPath('*/'.$returnUri.'/edit', ['id' => $id]);
        }
        //------------------------------------------------------------------//
    }
}
