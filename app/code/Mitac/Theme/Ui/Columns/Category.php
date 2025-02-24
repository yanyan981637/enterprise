<?php

namespace Mitac\Theme\Ui\Columns;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Api\CategoryRepositoryInterface;


class Category extends Column
{

    protected $categoryRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CategoryRepositoryInterface $categoryRepository,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->categoryRepository = $categoryRepository;
    }

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['category_page'])) {
                    $originalCategoryPage = $item['category_page'];
                    $item['category_page'] = '';
                    foreach (explode(',', $originalCategoryPage) as $categoryId) {
                        try {
                            $category = $this->categoryRepository->get($categoryId);
                            $item['category_page'] .= $category->getName() . '<br />';
                        }catch (\Exception $exception){
                            $item['category_page'] .= "<span style='color: red;'>".$exception->getMessage()."</span><br />";
                        }
                    }
                }
            }
        }

        return $dataSource;
    }

}
