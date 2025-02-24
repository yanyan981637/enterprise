<?php
namespace Mitac\Theme\Plugin\Page\Config;
use Magento\Cms\Helper\Page;
use Magento\Store\Model\ScopeInterface;
use Mitac\Theme\Api\Data\ColorInterface;
use Mitac\Theme\Helper\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

// TODO: remove log
class Renderer {

  private $registry;

  private $request;

  private $page_config;

  private $fileInfo;

  private $colorRepo;

  public $logger;

  protected $scopeConfig;

  protected $storeManager;

  public function __construct(
    \Magento\Framework\Registry $registry,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Framework\View\Page\Config $page_config,
    \Mitac\Theme\Helper\FileInfo $fileInfo,
    \Mitac\Theme\Api\ColorRepositoryInterface $colorRepo,
    ScopeConfigInterface $scopeConfig,
    StoreManagerInterface $storeManager
  ) {
    $this->registry = $registry;
    $this->request = $request;
    $this->page_config = $page_config;
    $this->fileInfo = $fileInfo;
    $this->colorRepo = $colorRepo;
    $this->scopeConfig = $scopeConfig;
    $this->storeManager = $storeManager;
    $this->logger = new Logger('change_color.log');
  }

  public function aroundPrepareFavicon(\Magento\Framework\View\Page\Config\Renderer $subject, callable $callback){

      $url = $this->request->getUri();

      if (str_contains($url, '/admin')) {
          return $callback();
      }

      $enableColorChange = $this->scopeConfig->getValue('web/color/enable', 'store');

      $this->logger->info('$enableColorChange: ' . $enableColorChange);

      if(!$enableColorChange){
          return $callback();
      }

      $this->logger->info($this->storeManager->getStore()->getId());

      $frontName = $this->request->getFullActionName();

      $this->logger->info('frontName: ' . $frontName);

      $storeID = $this->storeManager->getStore()->getId();
      $this->logger->info('storeID: ' . $storeID);

      $pageId = null;
      $pageType = ColorInterface::CATEGORY_PAGE;

     /**
      * TODO: 補好產品頁，首頁。404，blog頁，自定義url
      * */
      switch($frontName){
          case 'catalog_category_view': // category 頁面
              $current_category = $this->registry->registry("current_category");
              $this->logger->info('current_category: ' . $current_category->getId());
              $pageId = $current_category->getId();
              break;
//          case 'cms_index_index': // 首頁
//              $pageId = $this->scopeConfig->getValue(Page::XML_PATH_HOME_PAGE, ScopeInterface::SCOPE_STORE);
//              $this->logger->info('$pageId: ' . $pageId);
//              break;
          default:
              return $callback();
      }

      try {
          $colorTheme = $this->colorRepo->getColorByPage($pageId, $pageType, $storeID);

          if($colorTheme){
              $this->setTheme($colorTheme);
              return null;
          } else{
              return $callback();
          }
      }catch (\Exception $exception){
          $this->logger->error($exception->getMessage());
          return $callback();
      }
  }

  private function setTheme(ColorInterface $color)
  {
      $this->logger->info('setTheme: ' . $color->getColorId());
      $this->page_config->setElementAttribute("html", "theme-color", $color->getColorAttrName());
      $this->page_config->setElementAttribute('body','style',"--theme-color: ". $color->getColor());

      if ($this->fileInfo->isExist($color->getFaviconUrl())){
          $this->page_config->addRemotePageAsset(
              $this->fileInfo->getFileUrl($color->getFaviconUrl()),
              "link",
              ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
              'icon'
          );
      } else {
          throw new \Magento\Framework\Exception\LocalizedException('failed to load favicon');
      }


  }


}
