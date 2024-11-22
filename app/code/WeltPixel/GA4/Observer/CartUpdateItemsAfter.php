<?php
namespace WeltPixel\GA4\Observer;

use Magento\Framework\Event\ObserverInterface;

class CartUpdateItemsAfter implements ObserverInterface
{
    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \WeltPixel\GA4\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     */
    public function __construct(
        \WeltPixel\GA4\Helper\Data $helper,
        \Magento\Checkout\Model\Session $_checkoutSession
    )
    {
        $this->helper = $helper;
        $this->_checkoutSession = $_checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }

        $infoDataObject = $observer->getData('info');
        $cart = $observer->getData('cart');

        $data = $infoDataObject->getData();

        foreach ($data as $itemId => $itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                continue;
            }

            if ($item->getQtyBeforeChange() != $item->getQty()) {
                $qtyChange =  $item->getQty() - $item->getQtyBeforeChange();
                if ($qtyChange != 0) {
                    if ($qtyChange < 0) {
                        $currentRemoveToCartData = $this->_checkoutSession->getGA4RemoveFromCartData();
                        $removeFromCartPushData = $this->helper->removeFromCartPushData(abs($qtyChange), $item->getProduct(), $item);

                        $newRemoveFromCartPushData = $this->helper->mergeAddToCartPushData($currentRemoveToCartData, $removeFromCartPushData);
                        $this->_checkoutSession->setGA4RemoveFromCartData($newRemoveFromCartPushData);
                    } else {
                        $currentAddToCartData = $this->_checkoutSession->getGA4AddToCartData();
                        $addToCartPushData = $this->helper->addToCartPushData($qtyChange, $item->getProduct(), $item->getBuyRequest()->getData(), true);

                        $newAddToCartPushData = $this->helper->mergeAddToCartPushData($currentAddToCartData, $addToCartPushData);
                        $this->_checkoutSession->setGA4AddToCartData($newAddToCartPushData);
                    }
                }
            }
        }

        return $this;
    }
}
