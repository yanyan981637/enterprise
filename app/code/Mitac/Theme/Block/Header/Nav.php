<?php
namespace Mitac\Theme\Block\Header;
use Magento\Framework\View\Element\Template;
use Magezon\NinjaMenus\Helper\Menu;
class Nav extends Template
{

  const COMMONLINKS = 'common_links';
  const HEADERLINK = 'top-menu';

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

  public function getCommonLinks(){
    return $this->menuHelper->getMenuHtml(self::COMMONLINKS, 'common_links');
  }

  public function getheaderLinks(){
    return $this->menuHelper->getMenuHtml(self::HEADERLINK);
  }

}