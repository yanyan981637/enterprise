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
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductLabels\Block\Adminhtml\Label\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Generic implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->authorization = $authorization;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * @param int $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->authorization->isAllowed($resourceId);
    }

    /**
     * @return \Magezon\ProductLabels\Model\Label
     */
    public function getCurrentLabel()
    {
        return $this->registry->registry('productlabels_label');
    }

    /**
     * @param  array $params
     * @return array
     */
    public function getButtonAttribute($params = [])
    {
        $attributes = [
            'mage-init' => [
                'Magento_Ui/js/form/button-adapter' => [
                    'actions' => [
                        [
                            'targetName' => 'productlabels_label_form.productlabels_label_form',
                            'actionName' => 'save',
                            'params'     => $params
                        ]
                    ]
                ]
            ]
        ];

        return $attributes;
    }
}
