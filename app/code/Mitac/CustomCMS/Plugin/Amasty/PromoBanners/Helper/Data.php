<?php
namespace Mitac\CustomCMS\Plugin\Amasty\PromoBanners\Helper;

class Data {

  const POS_CHECKOUT_PAGE_TOP = 16;
  const POS_CHECKOUT_PAGE_BELOW_TITLE = 17;
  const POS_CHECKOUT_PAGE_BELOW_DESC = 18;
  const POS_CHECKOUT_PAGE_ABOVE_CHECKOUT_BUTTON = 19;
  const POS_CHECKOUT_PAGE_BELOW_ORDER_SUMMARY = 20;

  public function afterGetPosition(\Amasty\PromoBanners\Helper\Data $subject, $result){
    
    $addPosition = [
      self::POS_CHECKOUT_PAGE_TOP => 'Checkout Page(Top)',
      self::POS_CHECKOUT_PAGE_BELOW_TITLE => 'Checkout Page(Below Title)',
      self::POS_CHECKOUT_PAGE_BELOW_DESC => 'Checkout Page(Below Desc)',
      self::POS_CHECKOUT_PAGE_ABOVE_CHECKOUT_BUTTON => 'Checkout Page(Above Checkout Button)',
      self::POS_CHECKOUT_PAGE_BELOW_ORDER_SUMMARY => 'Checkout Page(Below Order Summary)',
    ];
    return $result + $addPosition;
  }

}