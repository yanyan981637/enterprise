<?php

namespace Mitac\SecondaryDevelopmentExtensions\Block\Blog\Post;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory as BlogCategoryCollectionFactory;
use Mageplaza\Blog\Block\Frontend;
class FilterByCategory extends Frontend
{
    protected $yearOptions = null;
    protected $currentCategory = null;

    public function getCurrentUrl(){
        $url = $this->_urlBuilder->getCurrentUrl();
        $urlParts = explode('?', $url);
        return $urlParts[0];
    }

    public function getCategoryFilterOptions(){

        $filterOption = [
            [
                'name' => __("All"),
                'url' => $this->getBlogRootUrl(),
                'active' => $this->checkoutRootCategoryActive()
            ]
        ];

        $allCategory = $this->getAllCategory();

        foreach ($allCategory as $category) {
            $filterOption[] = [
                'name' => $category->getName(),
                'url' => $this->helperData->getBlogUrl($category->getUrlKey(), 'category'),
                'active' => $this->checkoutActiveCategory($category->getId(), $category->getName())
            ];
        }

        return $filterOption;

    }

    public function getCurrentYear()
    {
        $year = $this->_request->getParam('year', null);

        return !empty($year) ? $year : 'all';

    }
    public function getYearOptions()
    {
        if(!$this->yearOptions){
            $dateArray = [];
            foreach ($this->getPostDate() as $postDate) {
                $dateArray[] = date("Y", $this->dateTime->timestamp($postDate));
            }
            $this->yearOptions = array_values(array_unique($dateArray));
        }



        return $this->yearOptions;
    }

    public function getCurrentCategory(){
        return $this->currentCategory;
    }

    protected function getBlogRootUrl()
    {
        return $this->helperData->getBlogUrl();
    }

    protected function checkoutRootCategoryActive()
    {
        if ("mpblog_post_index" == $this->getRequest()->getFullActionName('_')){
            $this->currentCategory = "All";
            return true;
        }
        return false;
    }

    protected function getAllCategory()
    {
        $categoryCollection = $this->categoryFactory->create()->getCollection();
        $storeId = $this->store->getStore()->getId();

        $categoryCollection
            ->addFieldToFilter('store_ids', [
                ['finset' => $storeId],
                ['finset' => 0]
            ])
            ->addFieldToFilter('enabled', 1)
            ->addFieldToFilter('level', [
                ['neq' => 0]
            ]);

        return $categoryCollection;
    }

    private function checkoutActiveCategory($categoryId, $categoryName){

        if(!$categoryId){
            return false;
        }

        $id = $this->getRequest()->getParam('id');

        if((int)$id == (int)$categoryId){
            $this->currentCategory = $categoryName;
            return true;
        }

        return false;

    }

    protected function getPostDate()
    {
        $posts     = $this->helperData->getPostList();
        $postDates = [];
        if ($posts->getSize()) {
            foreach ($posts as $post) {
                $postDates[] = $post->getPublishDate();
            }
        }
        return $postDates;
    }

}
