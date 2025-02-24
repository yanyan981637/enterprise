<?php

namespace Mitac\Theme\Ui\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\Blog\Helper\Data as BlogHelper;

class BlogPost extends Column
{
    protected $helperBlog;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        BlogHelper $helperBlog,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->helperBlog = $helperBlog;
    }

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['blog_page'])) {
                    $originalBlogPostPage = $item['blog_page'];
                    $item['blog_page'] = '';
                    foreach (explode(',', $originalBlogPostPage) as $blogPostId) {
                        try {
                            $post = $this->helperBlog->getFactoryByType(BlogHelper::TYPE_POST)->create()->load($blogPostId);
                            $item['blog_page'] .= $post->getName() . '<br />';
                        }catch (\Exception $exception){
                            $item['blog_page'] .= "<span style='color: red;'>" .$exception->getMessage()."</span><br />";
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
