<?php
namespace Mitac\Theme\Plugin\Page\Config;

class Renderer {

  private $registry;

  private $request;

  private $page_config;

  private $assetRepo;

  public function __construct(
    \Magento\Framework\Registry $registry,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Framework\View\Page\Config $page_config,
    \Magento\Framework\View\Asset\Repository $assetRepo
  ) {
    $this->registry = $registry;
    $this->request = $request;
    $this->page_config = $page_config;
    $this->assetRepo = $assetRepo;
  }

  public function aroundPrepareFavicon(\Magento\Framework\View\Page\Config\Renderer $subject, callable $callback){
    $frontName = $this->request->getFullActionName();


    if($frontName == "catalog_category_view"){

      $current_category = $this->registry->registry("current_category");
      $theme_color = $current_category->getThemeColor();

      $this->page_config->setElementAttribute("html", "theme-color", $theme_color);
      $this->page_config->addRemotePageAsset(
        $this->getThemeColorFaviconUrl($theme_color),
        "link",
        ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
        'icon'
      );
      $this->page_config->addRemotePageAsset(
      $this->getThemeColorFaviconUrl($theme_color),
      "link",
      ['attributes' => ['rel' => 'shortcut icon', 'type' => 'image/x-icon']],
      'shortcut-icon'
      );
      return;
    }
    return $callback();
  }

  private function getThemeColorFaviconUrl(string $color): string {
    
    switch ($color) {
      case 'green':
        return $this->assetRepo->getUrl('Mitac_Theme::images/Mio_logo_green_back.jpg');
      case 'orange':
      default:
        return $this->assetRepo->getUrl('Mitac_Theme::images/Mio_logo_orange_back.jpg');
    }
    
  }

}