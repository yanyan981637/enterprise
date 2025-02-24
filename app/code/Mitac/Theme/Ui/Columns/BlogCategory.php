<?php

namespace Mitac\Theme\Ui\Columns;

use Mageplaza\Blog\Model\ResourceModel\Category;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class BlogCategory extends Column
{
    protected $blogCategory;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Category $blogCategory,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->blogCategory = $blogCategory;
    }

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['blog_category_page'])) {
                    $originalBlogCategoryPage = $item['blog_category_page'];
                    $item['blog_category_page'] = '';
                    foreach (explode(',', $originalBlogCategoryPage) as $blogCategoryId) {
                        try {
                            $category = $this->blogCategory->getCategoryNameById($blogCategoryId);
                            $item['blog_category_page'] .= $category . '<br />';
                        }catch (\Exception $exception){
                            $item['blog_category_page'] .= "<span style='color: red;'>".$exception->getMessage()."</span><br />";
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
