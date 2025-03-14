<?php
namespace Mitac\SecondaryDevelopmentExtensions\Helper\Blog;
use Exception;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Api\Data\PostInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
class PostInfo extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var PostCollectionFactory
     * */

    public function __construct(
        HelperData $helperData,
        \Magento\Framework\App\Helper\Context $context,
        ProductCollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    )
    {
        $this->helperData = $helperData;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_redirect = $redirect;
        parent::__construct($context);
    }
    public function getPostDate($post)
    {
        $post_content_type = $post->getData('post_content_type');
        $date = '';

        switch ($post_content_type) {
            default:
            case "mitac_in_news":
            case 'normal_news':
                $date = $this->getDateFormat($post->getPublishDate());
                break;
            case "event":
                $date = $this->getDateFormat($post->getData('event_start_date')) . ' ~ ' . $this->getDateFormat($post->getData('event_end_date'));
                break;
        }

        return $date;
    }
    public function getCategoryByPost($post)
    {
        try {
            if (!$post->getCategoryIds()) {
                return null;
            }
            $categoryHtml = '';
            $categories = $this->helperData->getCategoryCollection($post->getCategoryIds());
            foreach ($categories as $_cat) {
                $categoryHtml .= "<span class='tag'>" . __($_cat->getName()) . "</span>";
            }
            return $categoryHtml;
        }catch (Exception $e) {
            return null;
        }
    }

    public function getPostUrl($post)
    {
        $post_content_type = $post->getData('post_content_type');
        $url = [];
        switch ($post_content_type) {
            default:
            case 'normal_news':
                $url = [
                    'link' => $post->getUrl(),
                    'target' => '_self',
                ];
                break;
            case "event":
            case "mitac_in_news":
                $url = [
                    'link' => $post->getData("post_external_link"),
                    'target' => '_target',
                ];
                break;
        }
        return $url;

    }
    public function getDateFormat($date, $monthly = false)
    {
        try {
            $date = $this->helperData->getDateFormat($date, $monthly);
        } catch (Exception $e) {
            $date = null;
        }

        return $date;
    }

    /**
     * // TODO：待補
     * */
    public function getRelatedProducts(PostInterface $post){

        $postId = $post->getId();
        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addStoreFilter()
            ->addAttributeToFilter('enable', 1);

        $productCollection->getSelect()
            ->join(
            ['product_post' => $productCollection->getTable('mageplaza_blog_post_product')],
            'e.entity_id = product_post.entity_id'
            )
            ->where('product_post.post_id = ' . $postId)
            ->order('product_post.position ASC');
        return $productCollection;
    }

    public function getLatestNews(int $exclude_post_id = null ,int $limit = 3) {
        $postCollection = $this->helperData->getPostCollection();
        if ($exclude_post_id) {
            $postCollection->addFieldToFilter('post_id', ['nin' => $exclude_post_id]);
        }
        $postCollection->addFieldToFilter('can_show_in_home', 1);
        $postCollection->getSelect()->limit($limit);
        return $postCollection;
    }

    public function getPreviousPage()
    {
        $url = $this->_redirect->getRefererUrl();
        $base_url = $this->_urlBuilder->getBaseUrl();

        return $url && $base_url !== $url ? $url : $this->helperData->getBlogUrl();  // 如果没有上一页链接，返回默认路径
    }

}
