<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magefan\Community\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\Registry;

class TranslateButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param null $authorization
     */
    public function __construct(
        Context $context,
        Registry $registry,
        $authorization = null
    ) {
        parent::__construct($context, $authorization);
        $this->registry = $registry;
    }

    /**
     * Return id of product
     *
     * @return int|null
     */
    private function getProductId()
    {
        return $this->registry->registry('current_product')->getId();
    }

    /**
     * Return buttonData
     *
     * @return buttonData
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getProductId()) {
            $data = [
                'label' => __('Translate'),
                'class' => 'translate',
                'on_click' => 'window.location=\'' . $this->getControllerUrl() . '\'',
                'sort_order' => 20
            ];
        }
        return $data;
    }

    /**
     * Return url of controller
     *
     * @return string
     */
    private function getControllerUrl()
    {
        return $this->getUrl('translationplus/product/translate', ['id' => $this->getProductId()]);
    }
}
