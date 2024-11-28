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
 * @package   Magezon_ProductAttachments
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\ProductAttachments\Ui\Component\Listing\Column\Icon;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magezon\ProductAttachments\Model\ResourceModel\Icon\CollectionFactory as IconCollectionFactory;

class Thumbnail extends Column
{
    const ALT_FIELD = 'title';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var IconCollectionFactory
     */
    protected $iconCollectionFactory;

    /**
     * @var array
     */
    protected $iconCollection;

    /**
     * IconThumbnail constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param IconCollectionFactory $iconCollectionFactory
     * @param array $components
     * @param array $iconCollection
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        IconCollectionFactory $iconCollectionFactory,
        array $components = [],
        array $iconCollection = [],
        array $data = []
    ) {
        $this->iconCollection = $iconCollection;
        $this->iconCollectionFactory = $iconCollectionFactory;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $url = '';
                if ($item['file_name'] != '') {
                    $url = $this->getUrlIcon($item['icon_id']);
                }
                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_orig_src'] = $url;
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

    protected function getUrlIcon($iconId)
    {
        if ($this->iconCollection == null) {
            $this->iconCollection = $this->iconCollectionFactory->create();
            return $this->iconCollection->getItemById($iconId)->getUrlIcon();
        } else {
            return $this->iconCollection->getItemById($iconId)->getUrlIcon();
        }
    }
}
