<?php
/*
 * @Date: 2024-05-24 10:08:38
 * @LastEditors: arvin
 * @LastEditTime: 2024-06-04 15:59:16
 * @FilePath: /app/code/Mitac/CustomCMS/Plugin/Block/Checkout/Index.php
 * @Description: remove telphone tooltip
 */
namespace Mitac\CustomCMS\Plugin\Block\Checkout;

class Index {
  public function afterGetJsLayout(
    \Magento\Checkout\Block\Onepage $subject,
    $result
  ){
    $jsLayout = json_decode($result, true);

    if($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['tooltip']){
      unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['tooltip']);
    }

    if($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['telephone']['config']['tooltip']){
      unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['telephone']['config']['tooltip']);
    }
    
    return json_encode($jsLayout, JSON_HEX_TAG);
  }
}