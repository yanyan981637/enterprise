<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Block\Adminhtml\Category\Edit;

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
     * Return id of category
     *
     * @return int|null
     */
    private function getCategoryId()
    {
        return $this->registry->registry('current_category')->getId();
    }

    /**
     * Return buttonData
     *
     * @return buttonData
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getCategoryId()) {
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
        return $this->getUrl('translationplus/category/translate', ['id' => $this->getCategoryId()]);
    }
}
