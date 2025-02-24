<?php

namespace Mitac\Theme\Ui\Options;
use Magento\Framework\Data\OptionSourceInterface;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
class BlogPost implements OptionSourceInterface
{
    private $blogPostCollection;
    public function __construct(
       Collection $blogPostCollection,
    )
    {
        $this->blogPostCollection = $blogPostCollection;
    }

    public function toOptionArray()
    {
        $collection = $this->blogPostCollection->load();
        $options = [];
        foreach ($collection as $blogPost) {
            $options[] = [
                'value' => $blogPost->getId(),
                'label' => $blogPost->getName(),
            ];
        }
        return $options;
    }
}
