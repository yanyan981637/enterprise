<?php
namespace Mitac\Theme\Controller\Adminhtml\Color;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Mitac\Theme\Api\ColorRepositoryInterface;
use Mitac\Theme\Api\Data\ColorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Mitac_Theme::color_save';

    /**
     * @var ColorRepositoryInterface
     */
    private $colorRepository;

    /**
     * @var ColorInterface
     */
    private $colorModel;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Context $context,
        ColorRepositoryInterface $colorRepository,
        ColorInterface $colorModel,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->colorRepository = $colorRepository;
        $this->colorModel = $colorModel;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addErrorMessage(__('Wrong request method type'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $data = $this->getRequest()->getPostValue();
            if (empty($data)) {
                throw new LocalizedException(__('Invalid form data.'));
            }

            $id = $this->getRequest()->getParam('color_id');
            $model = $id ? $this->colorRepository->getById($id) : $this->colorModel;

            // 移除空的 color_id，讓數據庫自動生成
            if (empty($id)) {
                unset($data['color_id']);
            }

            $preparedData = $this->prepareData($data);

            $model->setData($preparedData);
            $this->colorRepository->save($model);

            // 檢查保存後是否有 ID

            $this->messageManager->addSuccessMessage(__('The color has been saved successfully.'));
            $this->dataPersistor->clear('theme_color');

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['color_id' => $model->getId()]);
            }
            return $resultRedirect->setPath('*/*/');

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the color. Please try again.'));
        }

        $this->dataPersistor->set('theme_color', $data);
        return $resultRedirect->setPath('*/*/edit', ['color_id' => $id]);
    }

    /**
     * Prepare form data for saving
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        if (isset($data['favicon_url'])) {
            $data['favicon_url'] = $this->processFaviconUrl($data['favicon_url']);
        }

        return $data;
    }

    /**
     * Process favicon URL data
     *
     * @param mixed $faviconData
     * @return string
     */
    private function processFaviconUrl($faviconData): string
    {
        if (is_array($faviconData) &&
            !empty($faviconData) &&
            isset($faviconData[0]['url'])
        ) {
            $url = $faviconData[0]['url'];
            $baseMediaUrl = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
            // 如果URL包含基礎媒體URL，則移除它以獲得相對路徑
            if (strpos($url, $baseMediaUrl) === 0) {
                return substr($url, strlen($baseMediaUrl));
            }
            if (strpos($url, '/media/.renditions') === 0) {
                return str_replace('/media/.renditions/', '', $url);
            }
        }

        if(strpos($faviconData, '/media/.renditions') === 0) {
            return str_replace('/media/.renditions/', '', $faviconData);
        }

        return $faviconData;
    }
}
