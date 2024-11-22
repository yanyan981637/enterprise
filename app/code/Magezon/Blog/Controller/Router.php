<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\Blog\Helper\Data;
use Magezon\Blog\Model\PostManager;
use Magezon\Blog\Model\ResourceModel\Post\CollectionFactory;

class Router implements RouterInterface
{
    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var PostManager
     */
    protected $postManager;

    /**
     * @var CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory
     */
    protected $authorCollectionFactory;

    /**
     * @var \Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    protected $tagCollectionFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param ActionFactory $actionFactory
     * @param Registry $coreRegistry
     * @param Data $dataHelper
     * @param PostManager $postManager
     * @param CollectionFactory $postCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory $authorCollectionFactory
     * @param \Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ActionFactory $actionFactory,
        Registry $coreRegistry,
        Data $dataHelper,
        PostManager $postManager,
        CollectionFactory $postCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Author\CollectionFactory $authorCollectionFactory,
        \Magezon\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->actionFactory             = $actionFactory;
        $this->_coreRegistry             = $coreRegistry;
        $this->dataHelper                = $dataHelper;
        $this->postManager               = $postManager;
        $this->postCollectionFactory     = $postCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->authorCollectionFactory   = $authorCollectionFactory;
        $this->tagCollectionFactory      = $tagCollectionFactory;
        $this->_storeManager             = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function match(RequestInterface $request)
    {
        if (!$this->dispatched && $this->dataHelper->isEnabled()) {
            $pathInfo = trim($request->getPathInfo(), '/');
            $result   = $this->processUrlKey($request, $pathInfo);
            if ($result) {
                $request->setModuleName($result->getModuleName())
                    ->setControllerName($result->getControllerName())
                    ->setActionName($result->getActionName());
                if ($params = $result->getParams()) {
                    $request->setParams($params);
                }
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $pathInfo);
                $request->setDispatched(true);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }
        }
    }

    /**
     * @param $request
     * @param $identifier
     * @return false|DataObject|void
     * @throws NoSuchEntityException
     */
    protected function processUrlKey($request, $identifier)
    {
        $result = false;
        $route  = $this->dataHelper->getRoute();
        if (!$route) {
            return;
        }
        $paths = explode("/", $identifier);
        // https://domain.com/us/blog/fashion
        $index = array_search($route, $paths);
        for ($i=0; $i < $index; $i++) {
            unset($paths[$i]);
        }
        $paths = array_values($paths);
        $count = count($paths);

        if ($paths[0] != $route) {
            return;
        }

        if ($count == 1) {
            $result = new DataObject([
                'module_name'     => Data::ROUTER,
                'controller_name' => 'index',
                'action_name'     => 'index'
            ]);
        } else {
            $categoryRoute     = $this->dataHelper->getCategoryRoute();
            $authorRoute       = $this->dataHelper->getAuthorRoute();
            $tagRoute          = $this->dataHelper->getTagRoute();
            $searchRoute       = $this->dataHelper->getSearchRoute();
            $archiveRoute      = $this->dataHelper->getArchiveRoute();
            $keys              = array_filter([$categoryRoute, $tagRoute, $archiveRoute, $authorRoute, $searchRoute, '']);
            $postUseCategories = $this->dataHelper->getPostUseCategories();

            //http://domain.com/blog/spring-collection
            if ($count == 2) {
                if ($post = $this->getPost($paths[1])) {
                    $categories = $post->getCategoryList();
                    if (!$postUseCategories || ($postUseCategories && !count($categories))) {
                        $result = new DataObject([
                            'module_name'     => Data::ROUTER,
                            'controller_name' => 'post',
                            'action_name'     => 'view',
                            'params' => [
                                'id' => $post->getId()
                            ]
                        ]);
                        $this->_coreRegistry->register("current_post", $post);
                    }
                }
                if (!$result && !$categoryRoute) {
                    $category = $this->getCategory($paths[1]);
                    if ($category) {
                        $result = new DataObject([
                            'module_name'     => Data::ROUTER,
                            'controller_name' => 'category',
                            'action_name'     => 'view',
                            'params' => [
                                'id' => $category->getId()
                            ]
                        ]);
                        $this->_coreRegistry->register("current_blog_category", $category);
                    }
                }
            }

            if ($count == 4) {
                if (in_array($paths[1], $keys)) {
                    switch ($paths[1]) {

                        //http://domain.com/blog/archive/2019/10
                        case $archiveRoute:
                            $year  = (int) $paths[2];
                            $month = (int) $paths[3];
                            if ($month >= 1 && $month <= 12) {
                                if ($year && $month && $this->postManager->getPostCollectionByMonth($year, $month)->count()) {
                                    $result = new DataObject([
                                        'module_name'     => Data::ROUTER,
                                        'controller_name' => 'archive',
                                        'action_name'     => 'view',
                                        'params' => [
                                            'archive_type'  => 'month',
                                            'year'          => $year,
                                            'month'         => $month
                                        ]
                                    ]);
                                }
                            }
                            break;
                    }
                }
            }

            if ($count == 3) {
                if (in_array($paths[1], $keys)) {
                    switch ($paths[1]) {

                        //http://domain.com/blog/category/fashion
                        case $categoryRoute:
                            $author = $this->getCategory($paths[2]);
                            if ($author) {
                                $result = new DataObject([
                                    'module_name'     => Data::ROUTER,
                                    'controller_name' => 'category',
                                    'action_name'     => 'view',
                                    'params' => [
                                        'id' => $author->getId()
                                    ]
                                ]);
                                $this->_coreRegistry->register("current_blog_category", $author);
                            }
                            break;

                        //http://domain.com/blog/author/michael
                        case $authorRoute:
                            $author = $this->getAuthor($paths[2]);
                            if ($author) {
                                $result = new DataObject([
                                    'module_name'     => Data::ROUTER,
                                    'controller_name' => 'author',
                                    'action_name'     => 'view',
                                    'params' => [
                                        'id' => $author->getId()
                                    ]
                                ]);
                                $this->_coreRegistry->register("current_author", $author);
                            }
                            break;

                        //http://domain.com/blog/tag/fashion
                        case $tagRoute:
                            $tag = $this->getTag($paths[2]);
                            if ($tag) {
                                $result = new DataObject([
                                    'module_name'     => Data::ROUTER,
                                    'controller_name' => 'tag',
                                    'action_name'     => 'view',
                                    'params' => [
                                        'id' => $tag->getId()
                                    ]
                                ]);
                                $this->_coreRegistry->register("current_tag", $tag);
                            }
                            break;

                        //http://domain.com/blog/search/result
                        case $searchRoute:
                            if ($paths[2] == 'result') {
                                $result = new DataObject([
                                    'module_name'     => Data::ROUTER,
                                    'controller_name' => 'search',
                                    'action_name'     => 'result',
                                    'params' => [
                                        's' => trim($request->getParam('s'))
                                    ]
                                ]);
                            }
                            break;

                        //http://domain.com/blog/archive/2019
                        case $archiveRoute:
                            $year = (int) $paths[2];
                            if ($year && $this->postManager->getPostCollectionByYear($year)->count()) {
                                $result = new DataObject([
                                    'module_name'     => Data::ROUTER,
                                    'controller_name' => 'archive',
                                    'action_name'     => 'view',
                                    'params' => [
                                        'archive_type'  => 'year',
                                        'year'          => $year
                                    ]
                                ]);
                            }
                            break;
                    }
                } elseif ($postUseCategories) {
                    //http://domain.com/blog/fashion/spring-collection
                    //category: fashion
                    //post: spring-collection
                    if ($post = $this->getPost($paths[2])) {
                        $category = $post->getCategory();
                        if ($category && ($category->getIdentifier() == $paths[1])) {
                            $result = new DataObject([
                                'module_name'     => Data::ROUTER,
                                'controller_name' => 'post',
                                'action_name'     => 'view',
                                'params' => [
                                    'id' => $post->getId()
                                ]
                            ]);
                            $this->_coreRegistry->register("current_post", $post);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $urlKey
     * @return DataObject|void
     */
    private function getPost($urlKey)
    {
        $urlSuffix = $this->dataHelper->getPostUrlSuffix();
        if (($urlSuffix && $this->endsWith($urlKey, $urlSuffix)) || !$urlSuffix) {
            if($urlSuffix) {
                $urlKey = substr($urlKey, 0, -strlen(trim($urlSuffix)));
            }
            $collection = $this->postCollectionFactory->create();
            $collection->prepareCollection();
            $collection->addCategoryCollection();
            $collection->addTotalComments();
            $collection->addFieldToFilter('identifier', $urlKey);
            $post = $collection->getFirstItem();
            if ($post->getId()) {
                return $post;
            }
        }
    }

    /**
     * @return boolean
     */
    public function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 || (substr($haystack, -$length) === $needle);
    }

    /**
     * @param $urlKey
     * @return DataObject|void
     * @throws NoSuchEntityException
     */
    public function getCategory($urlKey)
    {
        $storeId =  $this->_storeManager->getStore()->getId();
        $urlSuffix = $this->dataHelper->getCategoryUrlSuffix();
        if (($urlSuffix && $this->endsWith($urlKey, $urlSuffix)) || !$urlSuffix) {
            if($urlSuffix) {
                $urlKey = substr($urlKey, 0, -strlen(trim($urlSuffix)));
            }
            $collection = $this->categoryCollectionFactory->create();
            $collection->prepareCollection($storeId);
            $collection->addFieldToFilter('identifier', $urlKey);
            $category = $collection->getFirstItem();
            if ($category->getId()) {
                return $category;
            }
        }
    }

    /**
     * @param $urlKey
     * @return DataObject|void
     */
    public function getAuthor($urlKey)
    {
        if (!$this->dataHelper->enableAuthorPage()) {
            return;
        }
        $urlSuffix = $this->dataHelper->getAuthorUrlSuffix();
        if (($urlSuffix && $this->endsWith($urlKey, $urlSuffix)) || !$urlSuffix) {
            if($urlSuffix) {
                $urlKey = substr($urlKey, 0, -strlen(trim($urlSuffix)));
            }
            $collection = $this->authorCollectionFactory->create();
            $collection->addIsActiveFilter();
            $collection->addFieldToFilter('identifier', $urlKey);
            $category = $collection->getFirstItem();
            if ($category->getId()) {
                return $category;
            }
        }
    }

    /**
     * @param $urlKey
     * @return DataObject|void
     */
    public function getTag($urlKey)
    {
        $urlSuffix = $this->dataHelper->getTagUrlSuffix();
        if (($urlSuffix && $this->endsWith($urlKey, $urlSuffix)) || !$urlSuffix) {
            if($urlSuffix) {
                $urlKey = substr($urlKey, 0, -strlen(trim($urlSuffix)));
            }
            $collection = $this->tagCollectionFactory->create();
            $collection->addIsActiveFilter();
            $collection->addFieldToFilter('identifier', $urlKey);
            $tag = $collection->getFirstItem();
            if ($tag->getId()) {
                return $tag;
            }
        }
    }
}
