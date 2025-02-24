<?php

namespace Mitac\Theme\Ui\Columns;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mitac\Theme\Helper\FileInfo;

class Image extends Column
{
    private $fileInfo;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FileInfo $fileInfo,
        array $components = [],
        array $data = []
    ){
        $this->fileInfo = $fileInfo;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['favicon_url'])) {
                    $originalFaviconUrl = $item['favicon_url'];

                    if($this->fileInfo->isExist($originalFaviconUrl)){
                        $realUrl = $this->fileInfo->getFileUrl($originalFaviconUrl);
                        $item['favicon_url'] = "<img style='max-width: 40px;height: auto;' src='$realUrl' alt='favicon' />";
                    }

                }
            }
        }
        return $dataSource;
    }
}
