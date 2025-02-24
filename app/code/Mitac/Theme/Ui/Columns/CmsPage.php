<?php

namespace Mitac\Theme\Ui\Columns;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CmsPage extends Column
{
    protected $cmsPageRepository;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PageRepositoryInterface $cmsPageRepository,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->cmsPageRepository = $cmsPageRepository;
    }

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['cms_page'])) {
                    $originalCmsPage = $item['cms_page'];
                    $item['cms_page'] = '';
                    foreach (explode(',', $originalCmsPage) as $cmsPageId) {
                        try {
                            $cmsPage = $this->cmsPageRepository->getById($cmsPageId);
                            $item['cms_page'] .= $cmsPage->getTitle() . '<br />';
                        }catch (\Exception $exception){
                            $item['cms_page'] .= "<span style='color: red;'>".$exception->getMessage()."</span><br />";
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
