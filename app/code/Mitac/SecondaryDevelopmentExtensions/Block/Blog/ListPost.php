<?php
namespace Mitac\SecondaryDevelopmentExtensions\Block\Blog;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\Blog\Model\Config\Source\DisplayType;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
use Mageplaza\Blog\Helper\Data;
class ListPost extends \Mageplaza\Blog\Block\Frontend
{

    protected $_category = null;


    /**
     * @return Collection
     * @throws LocalizedException
     */
    public function getPostCollection()
    {
        $collection = $this->getCollection()->addAttributeToSort('publish_date', 'desc');

        $fitler_year = $this->getRequest()->getParam('year');

        if($collection &&  !empty($fitler_year)){
            $collection->addFieldToFilter('publish_date', ['like' => $fitler_year . '%']);
        }

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'mpblog.post.pager');

            $perPageValues = (string) $this->helperData
                ->getDisplayConfig('pagination', $this->store->getStore()->getId());

            $perPageValues = explode(',', $perPageValues ?? '');
            $perPageValues = array_combine($perPageValues, $perPageValues);
            $pager->setAvailableLimit($perPageValues)
                ->setCollection($collection);

            $this->setChild('pager', $pager);
        }

        return $collection;
    }

    protected function getCollection(){

        if($this->getRequest()->getFullActionName() === 'mpblog_post_index'){
            return $this->helperData->getPostCollection(null, null, $this->store->getStore()->getId());
        }

        if ($category = $this->getBlogObject()) {
            return $this->helperData->getPostCollection(Data::TYPE_CATEGORY, $category->getId());
        }

        return null;
    }

    public function maxShortDescription($description)
    {
        if (is_string($description)) {
            $html = '';
            foreach (explode("\n", trim($description)) as $value) {
                $html .= '<p>' . $value . '</p>';
            }

            return $html;
        }

        return $description;
    }

    protected function getBlogObject()
    {
        if (!$this->_category) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $category = $this->helperData->getObjectByParam($id, null, Data::TYPE_CATEGORY);
                if ($category && $category->getId()) {
                    $this->_category = $category;
                }
            }
        }

        return $this->_category;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return bool
     */
    public function isGridView()
    {
        return $this->helperData->getPostViewPageConfig('display_style') == DisplayType::GRID;
    }


    protected function _prepareLayout()
    {
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $this->_storeManager->getStore()->getBaseUrl()
            ])
                ->addCrumb($this->helperData->getRoute(), $this->getBreadcrumbsData());
        }

        $this->applySeoCode();

        return parent::_prepareLayout();
    }

    protected function getBreadcrumbsData()
    {
        $data = [];

        if($this->getRequest()->getFullActionName() === 'mpblog_post_index'){
            $label = $this->helperData->getBlogName();

            $data = [
                'label' => $label,
                'title' => $label
            ];
        }

        $category = $this->getBlogObject();

        if($category){
            $data = [
                'label' => $category->getName(),
                'title' => $category->getName()
            ];
        }

        return $data;
    }

    public function applySeoCode()
    {
        $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($this->getBlogTitle(true))));

        $object      = $this->getBlogObject();
        $storeId     = $this->store->getStore()->getId();
        $description = $object ? $object->getMetaDescription() : $this->helperData->getBlogConfig('seo/meta_description', $storeId);
        $this->pageConfig->setDescription($description);

        $keywords = $object ? $object->getMetaKeywords() : $this->helperData->getBlogConfig('seo/meta_keywords', $storeId);
        $this->pageConfig->setKeywords($keywords);

        $robots = $object ? $object->getMetaRobots() : $this->helperData->getBlogConfig('seo/meta_robots', $storeId);
        $this->pageConfig->setRobots($robots);

        $url = $object ? $object->getUrl() : $this->helperData->getBlogConfig('seo/url_key', $storeId);

        if ($this->getRequest()->getFullActionName() === 'mpblog_post_view' && $url) {
            $this->pageConfig->addRemotePageAsset(
                $url,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->getBlogTitle());
        }

        return $this;
    }

    public function getTitleSeparator()
    {
        $separator = (string) $this->helperData->getConfigValue('catalog/seo/title_separator');

        return ' ' . $separator . ' ';
    }

    public function getBlogTitle($meta = false)
    {
        $pageTitle = $this->helperData->getDisplayConfig('name') ?: __('Blog');
        if ($meta) {
            $title = $this->helperData->getBlogConfig('seo/meta_title') ?: $pageTitle;

            return [$title];
        }

        return $pageTitle;
    }

}
