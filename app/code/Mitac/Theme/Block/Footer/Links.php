<?php
namespace Mitac\Theme\Block\Footer;
use Magento\Framework\View\Element\Template;
use Magezon\NinjaMenus\Helper\Menu;
class Links extends Template
{

  protected $_template = "Magento_Theme::html/footer/links.phtml";

  const FooterLinks = 'common_links';

  /**
   * @var \Magezon\NinjaMenus\Helper\Menu
   */
  protected $menuHelper;

  public function __construct(
    Template\Context $context,
    Menu $menuHelper,
    array $data = []
  ) {
    parent::__construct($context, $data);
    $this->menuHelper = $menuHelper;
  }

  public function getFooterLinks(){
    return $this->menuHelper->getMenuHtml(self::FooterLinks, 'footer_links');
  }

}