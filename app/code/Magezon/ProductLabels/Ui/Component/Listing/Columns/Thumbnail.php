<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_ProductLabels
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\ProductLabels\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const ALT_FIELD = 'name';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param ContextInterface                           $context            
     * @param UiComponentFactory                         $uiComponentFactory 
     * @param \Magento\Framework\UrlInterface            $urlBuilder         
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager       
     * @param array                                      $components         
     * @param array                                      $data               
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder    = $urlBuilder;
        $this->_storeManager = $storeManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    { 
        $editUrl = $this->getData('config/edit');
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName]) && $image = $item[$fieldName]) {
                    if (strpos($image, 'http') === false) {
                        $url = $this->_storeManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . $image;
                    } else {
                        $url = $image;
                    }
                    $item[$fieldName . '_src']      = $url;
                    $item[$fieldName . '_orig_src'] = $url;
                    $item[$fieldName . '_link']     = $this->urlBuilder->getUrl(
                        $editUrl,
                        ['label_id' => $item['label_id'], 'store' => $this->context->getRequestParam('store')]
                    );
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
